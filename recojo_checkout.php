<?php

if (!defined('ABSPATH'))
    exit;

function rt_recojo_persona_able_woocommerce_loading_css_js()
{
    // Check if WooCommerce plugin is active
    if (function_exists('is_woocommerce')) {
        // Check if it's any of WooCommerce page
        global $wp;
        if (is_checkout() && empty($wp->query_vars['order-received'])) {
            wp_register_script('recojo_script', plugins_url('js/recojo.js', __FILE__), array(), Version_RT_Recojo_Persona, true);
            wp_enqueue_script('recojo_script');
        }
    }
}

add_action('wp_enqueue_scripts', 'rt_recojo_persona_able_woocommerce_loading_css_js', 99);

function rt_recojo_persona_custom_wc_default_address_fields($fields)
{
    $fields['billing']['billing_recojo'] = [
        'type' => 'checkbox',
        'label' => __('Will someone else receive your order?', 'rt-recojo-persona'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'priority' => 110
    ];

    $fields['billing']['billing_recojo_nombre'] = [
        'type' => 'text',
        'label' => __('First name', 'rt-recojo-persona'),
        'required' => false,
        'class' => array('form-row-first'),
        'clear' => true,
        'priority' => 120
    ];

    $fields['billing']['billing_recojo_apellido'] = [
        'type' => 'text',
        'label' => __('Last name', 'rt-recojo-persona'),
        'required' => false,
        'class' => array('form-row-last'),
        'clear' => true,
        'priority' => 130
    ];

    $fields['billing']['billing_recojo_dni'] = [
        'type' => 'number',
        'label' => __('DNI', 'rt-recojo-persona'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'priority' => 140
    ];

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'rt_recojo_persona_custom_wc_default_address_fields');

function rt_recojo_persona_validate_checkout_field($fields)
{

    if ($_POST['billing_recojo'] === '1') {

        if (!$_POST['billing_recojo_nombre']) {
            wc_add_notice('<b>' . __('Please enter the Pick-up First name', 'rt-recojo-persona') . '</b> is a required field.', 'error');
        }

        if (!$_POST['billing_recojo_apellido']) {
            wc_add_notice('<b>' . __('Please enter the Pick-up Last name', 'rt-recojo-persona') . '</b> is a required field.', 'error');
        }

        if (!$_POST['billing_recojo_dni']) {
            wc_add_notice('<b>' . __('Please enter the Pick-up DNI', 'rt-recojo-persona') . '</b> is a required field.', 'error');
        }

        if ($_POST['billing_recojo_dni']) {
            if (strlen($_POST['billing_recojo_dni']) < 8) {
                wc_add_notice('<b>' . __('Please enter 8 digits of DNI', 'rt-recojo-persona') . '</b> is a required field.', 'error');
            }
        }

    }
}

add_action('woocommerce_checkout_process', 'rt_recojo_persona_validate_checkout_field');


function rt_recojo_persona_remove_checkout_optional_fields_label($field, $key, $args, $value)
{
    // Only on checkout page
    if (is_checkout() && !is_wc_endpoint_url()) {
        $optional = '&nbsp;<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
        switch ($key) {
            case 'billing_recojo_nombre':
            case 'billing_recojo_apellido':
            case 'billing_recojo_dni':
                $field = str_replace($optional, ' <abbr class="required">*</abbr>', $field);
                break;
        }
    }
    return $field;
}

add_filter('woocommerce_form_field', 'rt_recojo_persona_remove_checkout_optional_fields_label', 10, 4);

function rt_recojo_persona_save_checkout_field($order_id)
{
    if ($_POST['billing_recojo']) update_post_meta($order_id, '_recojo', sanitize_text_field($_POST['billing_recojo']));
    if ($_POST['billing_recojo_nombre']) update_post_meta($order_id, '_recojo_nombre', sanitize_text_field($_POST['billing_recojo_nombre']));
    if ($_POST['billing_recojo_apellido']) update_post_meta($order_id, '_recojo_apellido', sanitize_text_field($_POST['billing_recojo_apellido']));
    if ($_POST['billing_recojo_dni']) update_post_meta($order_id, '_recojo_dni', sanitize_text_field($_POST['billing_recojo_dni']));
}

add_action('woocommerce_checkout_update_order_meta', 'rt_recojo_persona_save_checkout_field');

function rt_recojo_persona_show_checkout_field_order($order)
{
    $order_id = $order->get_id();
    $recojo = get_post_meta($order_id, '_recojo', true);
    echo '<p><strong>' . __('Will someone else receive your order?', 'rt-recojo-persona') . '</strong> ' . (($recojo) ? "SI" : "NO") . '</p>';
    if (get_post_meta($order_id, '_recojo_nombre', true)) echo '<p><strong>' . __('Pick-up First name', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order_id, '_recojo_nombre', true) . '</p>';
    if (get_post_meta($order_id, '_recojo_apellido', true)) echo '<p><strong>' . __('Pick-up Last name', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order_id, '_recojo_apellido', true) . '</p>';
    if (get_post_meta($order_id, '_recojo_dni', true)) echo '<p><strong>' . __('Pick-up DNI', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order_id, '_recojo_dni', true) . '</p>';
}

add_action('woocommerce_admin_order_data_after_billing_address', 'rt_recojo_persona_show_checkout_field_order', 1, 1);

function rt_recojo_persona_show_checkout_field_emails($order, $sent_to_admin, $plain_text, $email)
{
    $recojo = get_post_meta($order->get_id(), '_recojo', true);
    echo '<p><strong>' . __('Will someone else receive your order?', 'rt-recojo-persona') . '</strong> ' . (($recojo) ? "SI" : "NO") . '</p>';
    if (get_post_meta($order->get_id(), '_recojo_nombre', true)) echo '<p><strong>' . __('Pick-up First name', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order->get_id(), '_recojo_nombre', true) . '</p>';
    if (get_post_meta($order->get_id(), '_recojo_apellido', true)) echo '<p><strong>' . __('Pick-up Last name', 'rt-recojo-persona') . ' :</strong> ' . strtoupper(get_post_meta($order->get_id(), '_recojo_apellido', true)) . '</p>';
    if (get_post_meta($order->get_id(), '_recojo_dni', true)) echo '<p><strong>' . __('Pick-up DNI', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order->get_id(), '_recojo_dni', true) . '</p>';
}

add_action('woocommerce_email_after_order_table', 'rt_recojo_persona_show_checkout_field_emails', 20, 4);

function rt_recojo_persona_show_custom_fields_thankyou($order_id)
{
    $recojo = get_post_meta($order_id, '_recojo', true);
    echo '<p><strong>' . __('Will someone else receive your order?', 'rt-recojo-persona') . '</strong> ' . (($recojo) ? "SI" : "NO") . '</p>';
    if (get_post_meta($order_id, '_recojo_nombre', true)) echo '<p><strong>' . __('Pick-up First name', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order_id, '_recojo_nombre', true) . '</p>';
    if (get_post_meta($order_id, '_recojo_apellido', true)) echo '<p><strong>' . __('Pick-up Last name', 'rt-recojo-persona') . '  :</strong> ' . get_post_meta($order_id, '_recojo_apellido', true) . '</p>';
    if (get_post_meta($order_id, '_recojo_dni', true)) echo '<p><strong>' . __('Pick-up DNI', 'rt-recojo-persona') . ' :</strong> ' . get_post_meta($order_id, '_recojo_dni', true) . '</p>';
}

add_action('woocommerce_thankyou', 'rt_recojo_persona_show_custom_fields_thankyou', 20);


function rt_recojo_persona_get_product_order($response, $object, $request)
{
    if (empty($response->data))
        return $response;

    $recojo = get_post_meta($response->data['id'], '_recojo', true);
    $response->data['billing']['recojo'] = ($recojo) ? 'SI' : 'NO';
    $response->data['billing']['recojo_first_name'] = get_post_meta($response->data['id'], '_recojo_nombre', true);
    $response->data['billing']['recojo_last_name'] = get_post_meta($response->data['id'], '_recojo_apellido', true);
    $response->data['billing']['recojo_dni'] = get_post_meta($response->data['id'], '_recojo_dni', true);
    return $response;
}

add_filter("woocommerce_rest_prepare_shop_order_object", "rt_recojo_persona_get_product_order", 10, 3);