<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Classic checkout fields; preserve original field and order meta keys. */
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
    if ( ! rt_recojo_enabled() ) { return $fields; }
    $fields['billing']['billing_recojo'] = array( 'type'=>'checkbox', 'label'=>__( '¿Otra persona recogerá tu pedido?', 'rt-recojo-persona' ), 'required'=>false, 'class'=>array('form-row-wide'), 'priority'=>110 );
    foreach ( array(
        'billing_recojo_nombre'=>array( 'label'=>__( 'Nombres de la persona autorizada', 'rt-recojo-persona' ), 'class'=>'form-row-first', 'type'=>'text', 'priority'=>120 ),
        'billing_recojo_apellido'=>array( 'label'=>__( 'Apellidos de la persona autorizada', 'rt-recojo-persona' ), 'class'=>'form-row-last', 'type'=>'text', 'priority'=>130 ),
        'billing_recojo_dni'=>array( 'label'=>__( 'DNI de la persona autorizada', 'rt-recojo-persona' ), 'class'=>'form-row-wide', 'type'=>'text', 'priority'=>140 ),
    ) as $key=>$config ) {
        $fields['billing'][$key] = array( 'type'=>$config['type'], 'label'=>$config['label'], 'class'=>array($config['class'],'rt-recojo-dependent'), 'required'=>false, 'priority'=>$config['priority'], 'custom_attributes'=> $key==='billing_recojo_dni' ? array('inputmode'=>'numeric','maxlength'=>'8','pattern'=>'[0-9]{8}') : array() );
    }
    return $fields;
} );
add_action( 'wp_enqueue_scripts', function() {
    if ( ! rt_recojo_enabled() || ! function_exists( 'is_checkout' ) || ! is_checkout() || ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) ) { return; }
    wp_enqueue_script( 'rt-recojo-checkout', plugins_url( 'js/recojo.js', __FILE__ ), array( 'jquery' ), Version_RT_Recojo_Persona, true );
} );
add_action( 'woocommerce_checkout_process', function() {
    if ( ! rt_recojo_enabled() || empty( $_POST['billing_recojo'] ) ) { return; }
    foreach ( array( 'billing_recojo_nombre'=>__( 'nombres', 'rt-recojo-persona' ), 'billing_recojo_apellido'=>__( 'apellidos', 'rt-recojo-persona' ) ) as $key=>$label ) {
        if ( empty( $_POST[$key] ) || ! is_string( $_POST[$key] ) || ! trim( sanitize_text_field( wp_unslash( $_POST[$key] ) ) ) ) { wc_add_notice( sprintf( __( 'Ingresa los %s de la persona que recogerá el pedido.', 'rt-recojo-persona' ), $label ), 'error' ); }
    }
    $dni = isset( $_POST['billing_recojo_dni'] ) && is_string( $_POST['billing_recojo_dni'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_recojo_dni'] ) ) : '';
    if ( ! preg_match( '/^[0-9]{8}$/D', $dni ) ) { wc_add_notice( __( 'El DNI de la persona autorizada debe contener exactamente 8 dígitos.', 'rt-recojo-persona' ), 'error' ); }
} );
add_action( 'woocommerce_checkout_create_order', function( $order, $data ) {
    if ( ! rt_recojo_enabled() || empty( $_POST['billing_recojo'] ) ) { return; }
    $order->update_meta_data( '_recojo', '1' );
    foreach ( array( 'billing_recojo_nombre'=>'_recojo_nombre', 'billing_recojo_apellido'=>'_recojo_apellido', 'billing_recojo_dni'=>'_recojo_dni' ) as $source=>$key ) {
        $value = isset( $_POST[$source] ) && is_string( $_POST[$source] ) ? sanitize_text_field( wp_unslash( $_POST[$source] ) ) : '';
        $order->update_meta_data( $key, $value );
    }
}, 10, 2 );
/** Previous versions stored postmeta; WooCommerce CRUD still reads those in legacy orders. */
function rt_recojo_order_detail_data( $order ) {
    if ( ! $order || ! is_a( $order, 'WC_Order' ) ) { return array(); }
    $values = array();
    foreach ( array( '_recojo', '_recojo_nombre', '_recojo_apellido', '_recojo_dni' ) as $key ) { $values[$key] = $order->get_meta( $key, true ); }
    if ( ! $values['_recojo'] && ! $values['_recojo_nombre'] && ! $values['_recojo_apellido'] && ! $values['_recojo_dni'] ) { return array(); }
    return $values;
}
function rt_recojo_order_detail_html( $order ) {
    $data = rt_recojo_order_detail_data( $order );
    if ( ! $data ) { return; }
    echo '<section class="rt-recojo-order-details"><h3>' . esc_html__( 'Recojo por otra persona', 'rt-recojo-persona' ) . '</h3>';
    foreach ( array( '_recojo_nombre'=>__( 'Nombres', 'rt-recojo-persona' ), '_recojo_apellido'=>__( 'Apellidos', 'rt-recojo-persona' ), '_recojo_dni'=>__( 'DNI', 'rt-recojo-persona' ) ) as $key=>$label ) {
        if ( $data[$key] !== '' ) { echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $data[$key] ) . '</p>'; }
    }
    echo '</section>';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'rt_recojo_order_detail_html' );
add_action( 'woocommerce_thankyou', function( $order_id ) { rt_recojo_order_detail_html( wc_get_order( $order_id ) ); }, 20 );
add_action( 'woocommerce_email_after_order_table', function( $order, $sent_to_admin, $plain_text ) {
    $data = rt_recojo_order_detail_data( $order ); if ( ! $data ) { return; }
    if ( $plain_text ) { echo "\n" . __( 'Recojo por otra persona', 'rt-recojo-persona' ) . "\n"; foreach ( array( '_recojo_nombre'=>'Nombres', '_recojo_apellido'=>'Apellidos', '_recojo_dni'=>'DNI' ) as $key=>$label ) { if ( $data[$key] !== '' ) { echo $label . ': ' . sanitize_text_field( $data[$key] ) . "\n"; } } }
    else { rt_recojo_order_detail_html( $order ); }
}, 20, 3 );
add_filter( 'woocommerce_rest_prepare_shop_order_object', function( $response, $order, $request ) {
    if ( ! is_object( $response ) || ! isset( $response->data['billing'] ) || ! is_array( $response->data['billing'] ) ) { return $response; }
    $data = rt_recojo_order_detail_data( $order ); if ( ! $data ) { return $response; }
    $response->data['billing']['recojo'] = $data['_recojo'] ? 'SI' : 'NO';
    $response->data['billing']['recojo_first_name'] = $data['_recojo_nombre'];
    $response->data['billing']['recojo_last_name'] = $data['_recojo_apellido'];
    $response->data['billing']['recojo_dni'] = $data['_recojo_dni'];
    return $response;
}, 10, 3 );
