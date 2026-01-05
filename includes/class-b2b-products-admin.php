<?php
/**
 * Admin class for B2B Products
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Products_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_b2b_products_delete', array($this, 'ajax_delete_product'));
        add_action('wp_ajax_b2b_categories_delete', array($this, 'ajax_delete_category'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'B2B产品管理', // Page title
            'B2B产品', // Menu title
            'manage_options', // Capability
            'b2b-products', // Menu slug
            array($this, 'products_list_page'), // Callback
            'dashicons-products', // Icon
            30 // Position
        );
        
        add_submenu_page(
            'b2b-products',
            '所有产品',
            '所有产品',
            'manage_options',
            'b2b-products',
            array($this, 'products_list_page')
        );
        
        add_submenu_page(
            'b2b-products',
            '添加产品',
            '添加产品',
            'manage_options',
            'b2b-products-add',
            array($this, 'add_product_page')
        );
        
        add_submenu_page(
            'b2b-products',
            '产品分类',
            '产品分类',
            'manage_options',
            'b2b-products-categories',
            array($this, 'categories_page')
        );
        
        add_submenu_page(
            'b2b-products',
            'B2B设置',
            'B2B设置',
            'manage_options',
            'b2b-products-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('b2b_products_settings', 'b2b_products_inquiry_url');
        register_setting('b2b_products_settings', 'b2b_products_inquiry_button_text');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'b2b-products') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            'b2b-products-admin',
            B2B_PRODUCTS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            B2B_PRODUCTS_VERSION,
            true
        );
        
        wp_enqueue_style(
            'b2b-products-admin',
            B2B_PRODUCTS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            B2B_PRODUCTS_VERSION
        );
        
        wp_localize_script('b2b-products-admin', 'b2bProducts', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('b2b_products_nonce'),
            'delete_confirm' => '确定要删除这个产品吗？'
        ));
    }
    
    /**
     * Products list page
     */
    public function products_list_page() {
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            if (current_user_can('manage_options')) {
                B2B_Products_Database::delete_product(intval($_GET['id']));
                echo '<div class="notice notice-success"><p>产品已删除</p></div>';
            }
        }
        
        $products = B2B_Products_Database::get_all_products();
        
        include B2B_PRODUCTS_PLUGIN_DIR . 'templates/admin/products-list.php';
    }
    
    /**
     * Add product page
     */
    public function add_product_page() {
        // Handle form submission
        if (isset($_POST['b2b_product_submit'])) {
            if (!isset($_POST['b2b_product_nonce']) || !wp_verify_nonce($_POST['b2b_product_nonce'], 'b2b_product_action')) {
                wp_die('安全验证失败');
            }
            
            $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $data = array(
                'product_name' => isset($_POST['product_name']) ? $_POST['product_name'] : '',
                'product_description' => isset($_POST['product_description']) ? $_POST['product_description'] : '',
                'product_images' => isset($_POST['product_images']) ? explode(',', $_POST['product_images']) : array(),
                'category_id' => isset($_POST['category_id']) && $_POST['category_id'] ? intval($_POST['category_id']) : null,
                'product_highlights' => isset($_POST['product_highlights']) ? $_POST['product_highlights'] : ''
            );
            
            if ($product_id) {
                B2B_Products_Database::update_product($product_id, $data);
                echo '<div class="notice notice-success"><p>产品已更新</p></div>';
            } else {
                B2B_Products_Database::insert_product($data);
                echo '<div class="notice notice-success"><p>产品已添加</p></div>';
            }
        }
        
        $product = null;
        if (isset($_GET['id'])) {
            $product = B2B_Products_Database::get_product(intval($_GET['id']));
            if ($product) {
                $product['product_images'] = maybe_unserialize($product['product_images']);
            }
        }
        
        include B2B_PRODUCTS_PLUGIN_DIR . 'templates/admin/product-form.php';
    }
    
    /**
     * Categories page
     */
    public function categories_page() {
        // Handle form submission
        if (isset($_POST['b2b_category_submit'])) {
            if (!isset($_POST['b2b_category_nonce']) || !wp_verify_nonce($_POST['b2b_category_nonce'], 'b2b_category_action')) {
                wp_die('安全验证失败');
            }
            
            $category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $data = array(
                'category_name' => isset($_POST['category_name']) ? $_POST['category_name'] : '',
                'category_description' => isset($_POST['category_description']) ? $_POST['category_description'] : '',
                'category_slug' => isset($_POST['category_slug']) ? $_POST['category_slug'] : '',
                'parent_id' => isset($_POST['parent_id']) && $_POST['parent_id'] ? intval($_POST['parent_id']) : null,
                'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0
            );
            
            if (empty($data['category_name'])) {
                echo '<div class="notice notice-error"><p>分类名称不能为空</p></div>';
            } else {
                if ($category_id) {
                    B2B_Products_Database::update_category($category_id, $data);
                    echo '<div class="notice notice-success"><p>分类已更新</p></div>';
                } else {
                    B2B_Products_Database::insert_category($data);
                    echo '<div class="notice notice-success"><p>分类已添加</p></div>';
                }
            }
        }
        
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            if (current_user_can('manage_options')) {
                B2B_Products_Database::delete_category(intval($_GET['id']));
                echo '<div class="notice notice-success"><p>分类已删除</p></div>';
            }
        }
        
        $category = null;
        if (isset($_GET['id']) && (!isset($_GET['action']) || $_GET['action'] !== 'delete')) {
            $category = B2B_Products_Database::get_category(intval($_GET['id']));
        }
        
        $categories = B2B_Products_Database::get_all_categories();
        $category_tree = B2B_Products_Database::get_category_tree();
        
        // Get product count for each category
        foreach ($categories as &$cat) {
            $cat['product_count'] = B2B_Products_Database::get_category_product_count($cat['id']);
        }
        
        // Build a map for quick lookup
        $categories_map = array();
        foreach ($categories as $cat) {
            $categories_map[$cat['id']] = $cat;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'add' || isset($_GET['action']) && $_GET['action'] === 'edit' || $category) {
            include B2B_PRODUCTS_PLUGIN_DIR . 'templates/admin/category-form.php';
        } else {
            include B2B_PRODUCTS_PLUGIN_DIR . 'templates/admin/categories-list.php';
        }
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['b2b_settings_submit'])) {
            if (!isset($_POST['b2b_settings_nonce']) || !wp_verify_nonce($_POST['b2b_settings_nonce'], 'b2b_settings_action')) {
                wp_die('安全验证失败');
            }
            
            update_option('b2b_products_inquiry_url', sanitize_text_field($_POST['inquiry_url']));
            update_option('b2b_products_inquiry_button_text', sanitize_text_field($_POST['inquiry_button_text']));
            echo '<div class="notice notice-success"><p>设置已保存</p></div>';
        }
        
        $inquiry_url = get_option('b2b_products_inquiry_url', '#contact');
        $inquiry_button_text = get_option('b2b_products_inquiry_button_text', 'Request Quote');
        
        include B2B_PRODUCTS_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    /**
     * AJAX delete product
     */
    public function ajax_delete_product() {
        check_ajax_referer('b2b_products_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id) {
            B2B_Products_Database::delete_product($id);
            wp_send_json_success('产品已删除');
        } else {
            wp_send_json_error('无效的产品ID');
        }
    }
    
    /**
     * AJAX delete category
     */
    public function ajax_delete_category() {
        check_ajax_referer('b2b_products_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id) {
            B2B_Products_Database::delete_category($id);
            wp_send_json_success('分类已删除');
        } else {
            wp_send_json_error('无效的分类ID');
        }
    }
}

