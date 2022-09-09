<?php

/**
 * Plugin Name:       Macro_booking
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Macro健身房APP用户预约接口
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            winter
 * Author URI:        /
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */


require_once 'Macro_api.php';



$MACRO = new Macro_api();

register_activation_hook(__FILE__, function (){});













