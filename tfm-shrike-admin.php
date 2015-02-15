<?php

/**
 * Made with ❤ by themesfor.me
 *
 * Administration panel for plugin
 */

class tfm_shrike_admin 
{
    /**
     * Setup hooks
     */
    public function __construct()
    {	
        // WooCommerce tabs
        add_action( 'woocommerce_settings_tabs_array', array( $this, 'add_admin_tab' ), 50);
        add_action( 'woocommerce_settings_tabs_tfm_shrike', array( $this, 'add_admin_tab_settings'));
        add_action( 'woocommerce_update_options_tfm_shrike', array( $this, 'update_settings' ));
    }

    /**
     * Add our tab to the WooCommerce settings
     *
     * @param array $tabs List of tabs
     * @return array List of tabs with our tab appended
     */
    public function add_admin_tab($tabs)
    {
        $tabs['tfm_shrike'] = __('Google Product Feed', 'woocommerce-settings-tab-tfm');
        return $tabs;
    }

    /**
     * Send the settings to the WooCommerce API
     */
    public function add_admin_tab_settings()
    {   
        woocommerce_admin_fields($this->get_settings());
    }

    /**
     * Update settings using WooCommerce API
     */
    public function update_settings()
    {
        woocommerce_update_options($this->get_settings());
    }

    /**
     * Get all settings in WooCommerce format
     *
     * @return array Settings in WooCommerce format accessible by 'tfm_shrike_settings' hook
     */
    private function get_settings()
    {
        // See options here: https://github.com/woothemes/woocommerce/blob/5dcd19f5fa133a25c7e025d7c73e04516bcf90da/includes/admin/class-wc-admin-settings.php#L195
        $settings = array(
            // Top header
            'description_heading' => array(
                'name' => __('Location of feed', 'tfm-google-product-feed'),
                'type' => 'title',
                'desc' => __('The feed with product ready for Google Merchants is under following address:<br /><br />', 'tfm-google-product-feed') . $this->get_feed_url(),
                'id' => 'tfm_shrike_description_heading'
            ),

            'header_end' => array(
                 'type' => 'sectionend',
                 'id' => 'tfm_shrike_description_heading'
            ),

            // Settings
            'description_settings' => array(
                'name' => __('Settings', 'tfm-google-product-feed'),
                'type' => 'title',
                'desc' => '',
                'id' => 'tfm_shrike_description_settings'
            ),

            'condition' => array(
                'name' => __('Product condition', 'tfm-google-product-feed'),
                'type' => 'select',
                'desc' => __('Default condition of items sold in store'),
                'options' => array(
                    'new' => __('New', 'tfm-google-product-feed'),
                    'used' => __('Used', 'tfm-google-product-feed'),
                    'refubrished' => __('Refubrished', 'tfm-google-product-feed'),
                ),
                'id' => 'tfm_shrike_setting_condition'
            ),

            'cotegory' => array(
                'name' => __('Product category', 'tfm-google-product-feed'),
                'type' => 'select',
                'desc' => __('Default category of items sold in store'),
                'options' => $this->get_google_categories(),
                'id' => 'tfm_shrike_setting_category',
                'css' => 'width: 30%',
            ),

            'product_type' => array(
                'name' => __('Product type', 'tfm-google-product-feed'),
                'type' => 'select',
                'desc' => __('Type of items sold in store'),
                'options' => array(
                    'none' => __('None', 'tfm-google-product-feed'),
                    'use_category' => __('Use wordpress category as a product type', 'tfm-google-product-feed'),
                ),
                'id' => 'tfm_shrike_setting_type',
                'css' => 'width: 30%',
            ),

            'settings_end' => array(
                 'type' => 'sectionend',
                 'id' => 'tfm_shrike_description_heading'
            ),
        );

        return apply_filters('tfm_shrike_settings', $settings);
    } 

    private function get_feed_url()
    {
        return get_site_url(null, '/?feed=google_feed');
    }

    private function get_google_categories()
    {
        $lang = get_bloginfo('language');
        
        $file = __DIR__ . '/categories/' . $lang . '.txt';

        if(!file_exists($file)) {
            $file = __DIR__ . '/categories/en-US.txt';
        }

        $categoriesFile = file($file);

        foreach($categoriesFile as $line) {
            if(substr($line, 0, 1) == '#') {
                continue;
            }
            $cleanLine = trim($line);
            $categories[$cleanLine] = $cleanLine;
        }

        return $categories;
    }
}

if (!defined('ABSPATH')) exit;

global $tfm_shrike_admin;
$tfm_shrike_admin = new tfm_shrike_admin();