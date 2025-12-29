<?php
/**
 * Plugin Name: B2B Products Showcase
 * Plugin URI: https://shopagg.com/wp-plugins/shopagg-b2b-products
 * Description: B2B行业产品展示插件，后台中文管理，前台英文展示，支持多图上传和询价功能
 * Version: 1.0.0
 * Author: 庄朋龙
 * Author URI: https://shopagg.com
 * Text Domain: b2b-products
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('B2B_PRODUCTS_VERSION', '1.0.0');
define('B2B_PRODUCTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('B2B_PRODUCTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('B2B_PRODUCTS_TABLE_NAME', 'b2b_products');
define('B2B_PRODUCTS_CATEGORIES_TABLE_NAME', 'b2b_product_categories');

// Include required files
require_once B2B_PRODUCTS_PLUGIN_DIR . 'includes/class-b2b-products-database.php';
require_once B2B_PRODUCTS_PLUGIN_DIR . 'includes/class-b2b-products-admin.php';
require_once B2B_PRODUCTS_PLUGIN_DIR . 'includes/class-b2b-products-frontend.php';

/**
 * Activation function
 */
function b2b_products_activate() {
    B2B_Products_Database::create_table();
    
    // Set default inquiry URL
    if (!get_option('b2b_products_inquiry_url')) {
        update_option('b2b_products_inquiry_url', '#contact');
    }
    
    // Set default inquiry button text
    if (!get_option('b2b_products_inquiry_button_text')) {
        update_option('b2b_products_inquiry_button_text', 'Request Quote');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Deactivation function
 */
function b2b_products_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'b2b_products_activate');
register_deactivation_hook(__FILE__, 'b2b_products_deactivate');

/**
 * Main plugin class
 */
class B2B_Products {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        // Load text domain for translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Initialize admin
        if (is_admin()) {
            B2B_Products_Admin::get_instance();
        }
        
        // Initialize frontend
        B2B_Products_Frontend::get_instance();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('b2b-products', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize the plugin
B2B_Products::get_instance();

