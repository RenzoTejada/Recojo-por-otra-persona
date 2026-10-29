<?php
/**
 * Plugin Name: Recojo por otra persona
 * Plugin URI: https://renzotejada.com/plugins/recojo-por-otra-persona/
 * Description: Permite indicar quién recogerá un pedido realizado en el checkout clásico de WooCommerce.
 * Version: 0.4.0
 * Author: Renzo Tejada
 * Author URI: https://renzotejada.com/
 * License: GPL-2.0-or-later
 * Text Domain: rt-recojo-persona
 * Domain Path: /language
 * Requires at least: 6.8
 * Tested up to: 7.1.4
 * Requires PHP: 7.4
 * WC requires at least: 8.2
 * WC tested up to: 11.2.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'Version_RT_Recojo_Persona', '0.4.0' );
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
add_action( 'init', function() { load_plugin_textdomain( 'rt-recojo-persona', false, dirname( plugin_basename( __FILE__ ) ) . '/language' ); } );
require_once __DIR__ . '/recojo_checkout.php';

function rt_recojo_enabled() { return 'yes' === get_option( 'rt_recojo_enabled', 'yes' ); }
function rt_recojo_settings_url() { return admin_url( 'admin.php?page=rt-recojo-persona' ); }
add_action( 'admin_menu', function () {
    if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
    add_submenu_page( 'woocommerce', __( 'Recojo por otra persona', 'rt-recojo-persona' ), __( 'Recojo por otra persona', 'rt-recojo-persona' ), 'manage_woocommerce', 'rt-recojo-persona', 'rt_recojo_render_settings' );
} );
add_action( 'admin_init', function() {
    register_setting( 'rt_recojo_settings', 'rt_recojo_enabled', array( 'type'=>'string', 'sanitize_callback'=>function( $v ) { return 'yes' === $v ? 'yes' : 'no'; }, 'default'=>'yes' ) );
} );
function rt_recojo_render_settings() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
    ?>
    <div class="wrap rt-recojo-admin">
      <div class="rt-recojo-banner"><img src="<?php echo esc_url( plugins_url( 'assets/admin-banner.webp', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Recojo por otra persona', 'rt-recojo-persona' ); ?>"></div>
      <div class="rt-recojo-title"><div><h1><?php esc_html_e( 'Recojo por otra persona', 'rt-recojo-persona' ); ?></h1><p><?php esc_html_e( 'Configura los datos de la persona autorizada para recoger el pedido.', 'rt-recojo-persona' ); ?></p></div><span>v0.4.0</span></div>
      <nav class="rt-recojo-tabs" aria-label="<?php esc_attr_e( 'Secciones', 'rt-recojo-persona' ); ?>"><a href="#rt-recojo-config" class="current"><?php esc_html_e( 'Configuración', 'rt-recojo-persona' ); ?></a><a href="https://renzotejada.com/contacto/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contacto y soporte', 'rt-recojo-persona' ); ?> ↗</a></nav>
      <form id="rt-recojo-config" action="options.php" method="post" class="rt-recojo-card">
        <?php settings_fields( 'rt_recojo_settings' ); ?>
        <h2><?php esc_html_e( 'Campos de recojo', 'rt-recojo-persona' ); ?></h2>
        <p><?php esc_html_e( 'Permite que el comprador indique nombres, apellidos y DNI de la persona que recogerá su pedido.', 'rt-recojo-persona' ); ?></p>
        <label class="rt-recojo-switch"><input type="hidden" name="rt_recojo_enabled" value="no"><input type="checkbox" name="rt_recojo_enabled" value="yes" <?php checked( rt_recojo_enabled() ); ?>> <strong><?php esc_html_e( 'Activar los campos en el checkout clásico', 'rt-recojo-persona' ); ?></strong></label>
        <div class="rt-recojo-info"><strong><?php esc_html_e( 'Datos solicitados', 'rt-recojo-persona' ); ?></strong><p><?php esc_html_e( 'Nombre, apellido y DNI (8 dígitos). Estos datos se almacenan en el pedido y se muestran en la administración, los correos y la confirmación de compra.', 'rt-recojo-persona' ); ?></p></div>
        <div class="rt-recojo-note"><?php esc_html_e( 'Checkout Blocks: aún no compatible. Esta versión utiliza los campos del checkout clásico y no modifica pedidos históricos.', 'rt-recojo-persona' ); ?></div>
        <?php submit_button( __( 'Guardar cambios', 'rt-recojo-persona' ) ); ?>
      </form>
    </div><?php
}
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'woocommerce_page_rt-recojo-persona' === $hook ) { wp_enqueue_style( 'rt-recojo-admin', plugins_url( 'assets/admin.css', __FILE__ ), array(), Version_RT_Recojo_Persona ); }
} );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
    array_unshift( $links, '<a href="' . esc_url( rt_recojo_settings_url() ) . '">' . esc_html__( 'Ajustes', 'rt-recojo-persona' ) . '</a>' );
    return $links;
} );
