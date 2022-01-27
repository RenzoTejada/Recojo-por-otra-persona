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
 * Version:           0.0.3
 * Author:            Renzo Tejada
 * Author URI:        https://renzotejada.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       rt-recojo-persona
 * Domain Path:       /language
 * WC tested up to:   6.1.1
 * WC requires at least: 2.6
 */
if (!defined('ABSPATH')) {
    exit;
}

$plugin_recojo_version = get_file_data(__FILE__, array('Version' => 'Version'), false);

define('Version_RT_Recojo_Persona', $plugin_recojo_version['Version']);


/*
 * CHECKOUT
 */
require dirname(__FILE__)."/recojo_checkout.php";