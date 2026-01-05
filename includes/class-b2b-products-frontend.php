<?php
/**
 * Frontend class for B2B Products
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Products_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('b2b_products', array($this, 'products_shortcode'));
        add_shortcode('b2b_products_categories', array($this, 'categories_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }
    
    /**
     * Add rewrite rules for product detail page
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^product/([0-9]+)/?$', 'index.php?b2b_product_id=$matches[1]', 'top');
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'b2b_product_id';
        return $vars;
    }
    
    /**
     * Template redirect for product detail page
     */
    public function template_redirect() {
        $product_id = get_query_var('b2b_product_id');
        
        if ($product_id) {
            // Enqueue styles and scripts before loading template
            $this->enqueue_frontend_scripts();
            
            $product = B2B_Products_Database::get_product($product_id);
            
            if ($product) {
                // Load product detail template
                $template = B2B_PRODUCTS_PLUGIN_DIR . 'templates/frontend/product-detail.php';
                if (file_exists($template)) {
                    include $template;
                    exit;
                }
            } else {
                // Product not found, redirect to 404
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
            }
        }
    }
    
    /**
     * Get product detail URL
     */
    public static function get_product_url($product_id) {
        $product_id = intval($product_id);
        return home_url("/product/{$product_id}/");
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'b2b-products-frontend',
            B2B_PRODUCTS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            B2B_PRODUCTS_VERSION
        );
        
        wp_enqueue_script(
            'b2b-products-frontend',
            B2B_PRODUCTS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            B2B_PRODUCTS_VERSION,
            true
        );
    }
    
    /**
     * Products shortcode
     */
    public function products_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => -1,
            'category' => '',
            'category_id' => '',
            'show_filters' => 'yes',
            'per_page' => '',
            'pagination' => 'yes'
        ), $atts);
        
        // Determine category - shortcode parameter has priority
        $category_slug = '';
        $category_id = null;
        $is_category_locked = false; // Whether category is locked by shortcode parameter
        
        // Priority 1: shortcode category_id parameter
        if (!empty($atts['category_id'])) {
            $category_id = intval($atts['category_id']);
            $category = B2B_Products_Database::get_category($category_id);
            if ($category) {
                $category_slug = $category['category_slug'];
                $is_category_locked = true;
            } else {
                $category_id = null; // Invalid category ID
            }
        }
        
        // Priority 2: shortcode category parameter (slug or ID)
        if (!$is_category_locked && !empty($atts['category'])) {
            $category_param = trim($atts['category']);
            
            // Check if it's numeric (category ID)
            if (is_numeric($category_param)) {
                $category_id = intval($category_param);
                $category = B2B_Products_Database::get_category($category_id);
                if ($category) {
                    $category_slug = $category['category_slug'];
                    $is_category_locked = true;
                } else {
                    $category_id = null;
                }
            } else {
                // It's a slug
                $category = B2B_Products_Database::get_category_by_slug($category_param);
                if ($category) {
                    $category_id = $category['id'];
                    $category_slug = $category['category_slug'];
                    $is_category_locked = true;
                }
            }
        }
        
        // Priority 3: URL parameter (only if shortcode didn't specify)
        if (!$is_category_locked && isset($_GET['category'])) {
            $url_category = sanitize_text_field($_GET['category']);
            if (!empty($url_category)) {
                // Check if it's numeric (category ID)
                if (is_numeric($url_category)) {
                    $category_id = intval($url_category);
                    $category = B2B_Products_Database::get_category($category_id);
                    if ($category) {
                        $category_slug = $category['category_slug'];
                    } else {
                        $category_id = null;
                    }
                } else {
                    // It's a slug
                    $category = B2B_Products_Database::get_category_by_slug($url_category);
                    if ($category) {
                        $category_id = $category['id'];
                        $category_slug = $category['category_slug'];
                    }
                }
            }
        }
        
        // Pagination settings
        $enable_pagination = $atts['pagination'] === 'yes';
        $per_page = !empty($atts['per_page']) ? intval($atts['per_page']) : 0;
        $use_limit = $atts['limit'] > 0 && !$enable_pagination;
        
        // If pagination is enabled, use per_page or default to 12
        if ($enable_pagination && $per_page <= 0) {
            $per_page = 12; // Default per page
        }
        
        // Get current page from URL
        $current_page = isset($_GET['b2b_page']) ? max(1, intval($_GET['b2b_page'])) : 1;
        
        // Get products with pagination or without
        $total_count = 0;
        $total_pages = 0;
        
        if ($enable_pagination && $per_page > 0) {
            // Use paginated query
            $products = B2B_Products_Database::get_products_paginated(array(
                'category_id' => $category_id,
                'per_page' => $per_page,
                'page' => $current_page
            ));
            
            // Get total count for pagination
            $total_count = B2B_Products_Database::get_products_count($category_id);
            $total_pages = ceil($total_count / $per_page);
        } else {
            // Get all products (legacy behavior)
            if ($category_id) {
                $products = B2B_Products_Database::get_products_by_category($category_id);
            } else {
                $products = B2B_Products_Database::get_all_products();
            }
            
            // Apply limit if specified
            if ($use_limit) {
                $products = array_slice($products, 0, $atts['limit']);
            }
        }
        
        $inquiry_url = get_option('b2b_products_inquiry_url', '#contact');
        $inquiry_button_text = get_option('b2b_products_inquiry_button_text', 'Request Quote');
        $categories = B2B_Products_Database::get_all_categories();
        
        // Process show_filters parameter - support yes/no, true/false, 1/0
        $show_filters_value = strtolower(trim($atts['show_filters']));
        $show_filters = in_array($show_filters_value, array('yes', 'true', '1', 'on'), true);
        
        // Pass category slug to template
        $atts['category'] = $category_slug;
        $atts['category_locked'] = $is_category_locked;
        $atts['pagination_enabled'] = $enable_pagination && $per_page > 0;
        $atts['current_page'] = $current_page;
        $atts['total_pages'] = $total_pages;
        $atts['total_count'] = $total_count;
        $atts['per_page'] = $per_page;
        $atts['show_filters'] = $show_filters; // Pass boolean value to template
        
        ob_start();
        include B2B_PRODUCTS_PLUGIN_DIR . 'templates/frontend/products-grid.php';
        return ob_get_clean();
    }
    
    /**
     * Categories shortcode - Display category tree
     */
    public function categories_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_count' => 'yes',
            'show_description' => 'no',
            'expand_all' => 'no',
            'link_to_products' => 'yes'
        ), $atts);
        
        // Get category tree
        $category_tree = B2B_Products_Database::get_category_tree();
        
        // Get all categories for product count
        $all_categories = B2B_Products_Database::get_all_categories();
        $categories_map = array();
        foreach ($all_categories as $cat) {
            $categories_map[$cat['id']] = B2B_Products_Database::get_category_product_count($cat['id']);
        }
        
        // Build products page URL
        $products_page_url = '';
        // Try to find a page with [b2b_products] shortcode
        $pages = get_pages(array('post_status' => 'publish'));
        foreach ($pages as $page) {
            if (has_shortcode($page->post_content, 'b2b_products')) {
                $products_page_url = get_permalink($page->ID);
                break;
            }
        }
        // If no page found, use current page or home
        if (empty($products_page_url)) {
            global $post;
            if ($post) {
                $products_page_url = get_permalink($post->ID);
            } else {
                $products_page_url = home_url();
            }
        }
        
        // Pass data to template
        $atts['show_count'] = $atts['show_count'] === 'yes';
        $atts['show_description'] = $atts['show_description'] === 'yes';
        $atts['expand_all'] = $atts['expand_all'] === 'yes';
        $atts['link_to_products'] = $atts['link_to_products'] === 'yes';
        
        ob_start();
        include B2B_PRODUCTS_PLUGIN_DIR . 'templates/frontend/categories-tree.php';
        return ob_get_clean();
    }
}

