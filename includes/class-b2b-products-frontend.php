<?php
/**
 * Frontend class for B2B Products
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Products_Frontend {
    
    private static $instance = null;
    private $current_search_term = '';
    
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
        add_filter('posts_search', array($this, 'add_products_to_search'), 10, 2);
        add_filter('posts_results', array($this, 'merge_products_to_search_results'), 10, 2);
        add_filter('post_link', array($this, 'filter_product_permalink'), 10, 2);
        add_filter('the_title', array($this, 'filter_product_title'), 10, 2);
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
        
        // Build products page URL from settings
        $products_page_id = get_option('b2b_products_page_id', 0);
        $products_page_url = '';
        
        if ($products_page_id > 0) {
            $products_page_url = get_permalink($products_page_id);
        }
        
        // If no page set in settings, use current page or home as fallback
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
    
    /**
     * Add products to search query
     * This modifies the search SQL to include products
     */
    public function add_products_to_search($search, $query) {
        // Only modify search queries on frontend
        if (is_admin() || !$query->is_search() || !$query->is_main_query()) {
            return $search;
        }
        
        $search_term = $query->get('s');
        if (empty($search_term)) {
            return $search;
        }
        
        // Store search term for later use
        $this->current_search_term = $search_term;
        
        return $search;
    }
    
    /**
     * Merge products into search results
     */
    public function merge_products_to_search_results($posts, $query) {
        // Only modify search queries on frontend
        if (is_admin() || !$query->is_search() || !$query->is_main_query()) {
            return $posts;
        }
        
        $search_term = $query->get('s');
        if (empty($search_term)) {
            return $posts;
        }
        
        // Search products
        $products = $this->search_products($search_term);
        
        if (empty($products)) {
            return $posts;
        }
        
        // Convert products to post-like objects
        $product_posts = array();
        foreach ($products as $product) {
            $product_post = $this->product_to_post_object($product);
            if ($product_post) {
                $product_posts[] = $product_post;
            }
        }
        
        // Merge products with existing posts
        $merged_posts = array_merge($posts, $product_posts);
        
        // Sort by relevance (you can customize this)
        usort($merged_posts, function($a, $b) use ($search_term) {
            $a_title = strtolower($a->post_title);
            $b_title = strtolower($b->post_title);
            $search_lower = strtolower($search_term);
            
            // Exact match first
            $a_exact = ($a_title === $search_lower) ? 0 : 1;
            $b_exact = ($b_title === $search_lower) ? 0 : 1;
            
            if ($a_exact !== $b_exact) {
                return $a_exact - $b_exact;
            }
            
            // Starts with search term
            $a_starts = (strpos($a_title, $search_lower) === 0) ? 0 : 1;
            $b_starts = (strpos($b_title, $search_lower) === 0) ? 0 : 1;
            
            if ($a_starts !== $b_starts) {
                return $a_starts - $b_starts;
            }
            
            return 0;
        });
        
        return $merged_posts;
    }
    
    /**
     * Search products by term
     */
    private function search_products($search_term) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE product_name LIKE %s 
            OR product_description LIKE %s 
            OR product_highlights LIKE %s
            ORDER BY 
                CASE 
                    WHEN product_name LIKE %s THEN 1
                    WHEN product_highlights LIKE %s THEN 2
                    ELSE 3
                END,
                id DESC",
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            $search_term
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Convert product array to post-like object
     */
    private function product_to_post_object($product) {
        if (empty($product) || empty($product['id'])) {
            return null;
        }
        
        // Create a fake post object
        $post = new stdClass();
        $post->ID = 1000000 + intval($product['id']); // Use high ID to avoid conflicts
        $post->post_author = 1;
        $post->post_date = $product['created_at'];
        $post->post_date_gmt = $product['created_at'];
        $post->post_content = wp_kses_post($product['product_description']);
        $post->post_title = $product['product_name'];
        $post->post_excerpt = wp_trim_words(strip_tags($product['product_description']), 30);
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_password = '';
        $post->post_name = 'b2b-product-' . $product['id'];
        $post->to_ping = '';
        $post->pinged = '';
        $post->post_modified = $product['updated_at'];
        $post->post_modified_gmt = $product['updated_at'];
        $post->post_content_filtered = '';
        $post->post_parent = 0;
        $post->guid = B2B_Products_Frontend::get_product_url($product['id']);
        $post->menu_order = 0;
        $post->post_type = 'b2b_product';
        $post->post_mime_type = '';
        $post->comment_count = 0;
        $post->filter = 'raw';
        
        // Store original product data
        $post->b2b_product_data = $product;
        
        return $post;
    }
    
    /**
     * Filter product permalink in search results
     */
    public function filter_product_permalink($permalink, $post) {
        if (is_object($post) && isset($post->post_type) && $post->post_type === 'b2b_product' && isset($post->b2b_product_data)) {
            return B2B_Products_Frontend::get_product_url($post->b2b_product_data['id']);
        }
        if (is_numeric($post)) {
            $post_obj = get_post($post);
            if ($post_obj && isset($post_obj->post_type) && $post_obj->post_type === 'b2b_product' && isset($post_obj->b2b_product_data)) {
                return B2B_Products_Frontend::get_product_url($post_obj->b2b_product_data['id']);
            }
        }
        return $permalink;
    }
    
    /**
     * Filter product title in search results
     */
    public function filter_product_title($title, $post_id) {
        $post = get_post($post_id);
        if ($post && isset($post->post_type) && $post->post_type === 'b2b_product' && isset($post->b2b_product_data)) {
            return $post->b2b_product_data['product_name'];
        }
        return $title;
    }
}

