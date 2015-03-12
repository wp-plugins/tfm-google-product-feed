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
        add_action('woocommerce_settings_tabs_array', array( $this, 'add_admin_tab' ), 50);
        add_action('woocommerce_settings_tabs_tfm_shrike', array( $this, 'add_admin_tab_settings'));
        add_action('woocommerce_update_options_tfm_shrike', array( $this, 'update_settings' ));

        // Product options
        add_action('admin_init', array($this, 'admin_init'));
        
    }

    /**
     * Init all things required for admin
     */
    public function admin_init()
    {
        add_meta_box('tfm-shrike', __( 'TFM Google Product Feed', 'tfm-google-product-feed' ), array( $this, 'product_meta_box' ), 'product', 'advanced');
        add_action('save_post', array($this, 'save_product'));
    }

    public function product_meta_box()
    {   
        global $post;
        
        $tfm_settings = get_post_meta($post->ID, 'tfm_shrike_settings', true);

        echo $this->get_form_input('gtin', 'Product GTIN', 'Global Trade Item Numbers (GTIN) include <strong>UPC</strong>, <strong>EAN</strong> (in Europe), <strong>JAN</strong> and <strong>ISBN</strong>', 'GTIN', isset($tfm_settings['gtin']) ? $tfm_settings['gtin'] : '');
        echo $this->get_form_input('mpn', 'Product MPN', 'Manufacturer Part Number', 'MPN', isset($tfm_settings['mpn']) ? $tfm_settings['mpn'] : '');
        echo $this->get_form_input('brand', 'Product brand', '', 'Brand', isset($tfm_settings['brand']) ? $tfm_settings['brand'] : '');

        echo '<input type="hidden" name="tfm_settings" value="1" />';
    }

    public function save_product($id)
    {
        if(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
            return;
        }

        if(empty($_POST['tfm_settings'])) {
            return;
        }
        //update_post_meta($id, 'tfm_shrike_settings', array('test' => 'ok'));
        $tfm_settings = get_post_meta($id, 'tfm_shrike_settings', true);

        if(empty($tfm_settings)) {
            $tfm_settings = array();
        }

        $tfm_settings['gtin'] = sanitize_text_field($_POST['tfm_gtin']);
        $tfm_settings['mpn'] = sanitize_text_field($_POST['tfm_mpn']);
        $tfm_settings['brand'] = sanitize_text_field($_POST['tfm_brand']);

        update_post_meta($id, 'tfm_shrike_settings', $tfm_settings);
    }

    /**
     * Add our tab to the WooCommerce settings
     *
     * @param array $tabs List of tabs
     * @return array List of tabs with our tab appended
     */
    public function add_admin_tab($tabs)
    {
        $tabs['tfm_shrike'] = __('Google Product Feed', 'tfm-google-product-feed');
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
                'desc' => __('The feed with product ready for Google Merchants is under following address:<br /><br />', 'tfm-google-product-feed') . sprintf('<a href="%s">%s</a>', $this->get_feed_url(), $this->get_feed_url()),
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


    /**
     * Helper for generating HTML inputs
     */
    private function get_form_input($name, $description, $help, $placeholder, $value)
    {
        ob_start();

        ?>
            <p>
                <label for="tfm-<?php echo $name; ?>"><?php _e($description, 'tfm-google-product-feed'); ?></label> - <small><?php echo $help; ?></small><br />
                <input type="text" name="tfm_<?php echo $name; ?>" id="tfm-<?php echo $name; ?>" value="<?php echo $value; ?>" placeholder="<?php _e($placeholder, 'tfm-google-product-feed' ); ?>" />
            </p>
        <?php

        return ob_get_clean();
    }
}

if (!defined('ABSPATH')) exit;

global $tfm_shrike_admin;
$tfm_shrike_admin = new tfm_shrike_admin();