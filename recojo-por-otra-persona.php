<?php

/**
 *
 * @link              https://renzotejada.com/
 * @package           Recojo por otra persona
 *
 * @wordpress-plugin
 * Plugin Name:       Recojo por otra persona
 * Plugin URI:        https://renzotejada.com/recojo-por-otra-persona
 * Description:       It will allow you the option of pick up by another person.
 * Version:           0.3
 * Author:            Renzo Tejada
 * Author URI:        https://renzotejada.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rt-recojo-persona
 * Domain Path:       /language
 * WC tested up to:   9.5.2
 * WC requires at least: 2.6
 */
if (!defined('ABSPATH')) {
    exit;
}

$plugin_recojo_version = get_file_data(__FILE__, array('Version' => 'Version'), false);

define('Version_RT_Recojo_Persona', $plugin_recojo_version['Version']);

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/*
 * CHECKOUT
 */
require dirname(__FILE__)."/recojo_checkout.php";