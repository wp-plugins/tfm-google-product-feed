<?php
/*
Plugin Name: themesFor.me Google Product Feed for WooCommerce
Plugin URI: http://themesfor.me/wordpress-plugin-woocommerce-google-product-feed
Description: Google Product Feed for Google Merchants integration
Version: 1.0.7
Author: Themes For Me
Author URI: http://themesfor.me/
License: GPL2
 */
if (!defined('ABSPATH')) exit;

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Tools
    require_once('tfm-xml-tools.php');

    // Admin
    if(is_admin()) {
        require_once('tfm-shrike-admin.php');
    }

    // Feed
    require_once('tfm-shrike-feed.php');

}
