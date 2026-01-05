<?php
/**
 * Database class for B2B Products
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Products_Database {
    
    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        $categories_table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create products table with latest structure
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_name varchar(255) NOT NULL,
            product_description text,
            product_images longtext,
            category_id bigint(20) DEFAULT NULL,
            product_highlights varchar(200) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Create categories table with latest structure
        $categories_sql = "CREATE TABLE IF NOT EXISTS $categories_table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            category_name varchar(255) NOT NULL,
            category_description text,
            category_slug varchar(255) NOT NULL,
            parent_id bigint(20) DEFAULT NULL,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY category_slug (category_slug),
            KEY parent_id (parent_id)
        ) $charset_collate;";
        
        dbDelta($categories_sql);
        
        // Add parent_id column if it doesn't exist (for existing installations)
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $categories_table_name LIKE 'parent_id'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $categories_table_name ADD COLUMN parent_id bigint(20) DEFAULT NULL AFTER category_slug");
            $wpdb->query("ALTER TABLE $categories_table_name ADD KEY parent_id (parent_id)");
        }
        
        // Ensure indexes exist for products table
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists) {
            $indexes = $wpdb->get_results("SHOW INDEXES FROM $table_name WHERE Key_name = 'category_id'");
            if (empty($indexes)) {
                $wpdb->query("ALTER TABLE $table_name ADD INDEX category_id (category_id)");
            }
        }
    }
    
    /**
     * Get all products
     */
    public static function get_all_products() {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
    }
    
    /**
     * Get product by ID
     */
    public static function get_product($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    }
    
    /**
     * Insert product
     */
    public static function insert_product($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        // Sanitize product_highlights - only plain text, max 200 characters
        $product_highlights = '';
        if (isset($data['product_highlights']) && !empty($data['product_highlights'])) {
            $product_highlights = sanitize_text_field($data['product_highlights']);
            $product_highlights = mb_substr($product_highlights, 0, 200);
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'product_name' => sanitize_text_field($data['product_name']),
                'product_description' => wp_kses_post($data['product_description']),
                'product_images' => maybe_serialize($data['product_images']),
                'category_id' => isset($data['category_id']) && $data['category_id'] ? intval($data['category_id']) : null,
                'product_highlights' => $product_highlights
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update product
     */
    public static function update_product($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        // Sanitize product_highlights - only plain text, max 200 characters
        $product_highlights = '';
        if (isset($data['product_highlights']) && !empty($data['product_highlights'])) {
            $product_highlights = sanitize_text_field($data['product_highlights']);
            $product_highlights = mb_substr($product_highlights, 0, 200);
        }
        
        return $wpdb->update(
            $table_name,
            array(
                'product_name' => sanitize_text_field($data['product_name']),
                'product_description' => wp_kses_post($data['product_description']),
                'product_images' => maybe_serialize($data['product_images']),
                'category_id' => isset($data['category_id']) && $data['category_id'] ? intval($data['category_id']) : null,
                'product_highlights' => $product_highlights
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%d', '%s'),
            array('%d')
        );
    }
    
    /**
     * Delete product
     */
    public static function delete_product($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }
    
    /**
     * Get all products by category
     */
    public static function get_products_by_category($category_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        if ($category_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE category_id = %d ORDER BY id DESC",
                $category_id
            ), ARRAY_A);
        }
        
        return self::get_all_products();
    }
    
    /**
     * Get products count by category
     */
    public static function get_products_count($category_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        if ($category_id) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE category_id = %d",
                $category_id
            ));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    /**
     * Get paginated products
     */
    public static function get_products_paginated($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        $defaults = array(
            'category_id' => null,
            'per_page' => 10,
            'page' => 1
        );
        
        $args = wp_parse_args($args, $defaults);
        $per_page = intval($args['per_page']);
        $page = max(1, intval($args['page']));
        $offset = ($page - 1) * $per_page;
        
        $where = '';
        $values = array();
        
        if ($args['category_id']) {
            $where = "WHERE category_id = %d";
            $values[] = intval($args['category_id']);
        }
        
        $query = "SELECT * FROM $table_name $where ORDER BY id DESC LIMIT %d OFFSET %d";
        
        // Always add per_page and offset to values
        $values[] = $per_page;
        $values[] = $offset;
        
        // Prepare query with appropriate number of placeholders
        if ($args['category_id']) {
            // 3 placeholders: category_id, per_page, offset
            $prepared_query = $wpdb->prepare($query, $values[0], $values[1], $values[2]);
        } else {
            // 2 placeholders: per_page, offset
            $prepared_query = $wpdb->prepare($query, $per_page, $offset);
        }
        
        return $wpdb->get_results($prepared_query, ARRAY_A);
    }
    
    // ==================== Category Methods ====================
    
    /**
     * Get all categories
     */
    public static function get_all_categories() {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY sort_order ASC, id ASC", ARRAY_A);
    }
    
    /**
     * Get categories by parent ID
     */
    public static function get_categories_by_parent($parent_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        
        if ($parent_id === null) {
            // Get top-level categories (parent_id is NULL)
            return $wpdb->get_results("SELECT * FROM $table_name WHERE parent_id IS NULL ORDER BY sort_order ASC, id ASC", ARRAY_A);
        } else {
            // Get child categories of a specific parent
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE parent_id = %d ORDER BY sort_order ASC, id ASC",
                $parent_id
            ), ARRAY_A);
        }
    }
    
    /**
     * Get category tree (hierarchical structure)
     */
    public static function get_category_tree() {
        $all_categories = self::get_all_categories();
        $tree = array();
        $categories_by_id = array();
        
        // Index categories by ID
        foreach ($all_categories as $category) {
            $categories_by_id[$category['id']] = $category;
            $categories_by_id[$category['id']]['children'] = array();
        }
        
        // Build tree structure
        foreach ($all_categories as $category) {
            $parent_id = isset($category['parent_id']) ? intval($category['parent_id']) : null;
            if ($parent_id && $parent_id > 0 && isset($categories_by_id[$parent_id])) {
                $categories_by_id[$parent_id]['children'][] = &$categories_by_id[$category['id']];
            } else {
                $tree[] = &$categories_by_id[$category['id']];
            }
        }
        
        return $tree;
    }
    
    /**
     * Get category ancestors (parent chain)
     */
    public static function get_category_ancestors($category_id) {
        $ancestors = array();
        $category = self::get_category($category_id);
        
        while ($category) {
            $parent_id = isset($category['parent_id']) ? intval($category['parent_id']) : null;
            if ($parent_id && $parent_id > 0) {
                $parent = self::get_category($parent_id);
                if ($parent) {
                    array_unshift($ancestors, $parent);
                    $category = $parent;
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        
        return $ancestors;
    }
    
    /**
     * Get category descendants (all children recursively)
     */
    public static function get_category_descendants($category_id) {
        $descendants = array();
        $children = self::get_categories_by_parent($category_id);
        
        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, self::get_category_descendants($child['id']));
        }
        
        return $descendants;
    }
    
    /**
     * Get category by ID
     */
    public static function get_category($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    }
    
    /**
     * Get category by slug
     */
    public static function get_category_by_slug($slug) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE category_slug = %s", $slug), ARRAY_A);
    }
    
    /**
     * Insert category
     */
    public static function insert_category($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        
        // Generate slug if not provided
        if (empty($data['category_slug'])) {
            $slug = sanitize_title($data['category_name']);
            $original_slug = $slug;
            $counter = 1;
            while ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE category_slug = %s", $slug))) {
                $slug = $original_slug . '-' . $counter;
                $counter++;
            }
            $data['category_slug'] = $slug;
        }
        
        $parent_id_value = (isset($data['parent_id']) && $data['parent_id'] !== '' && $data['parent_id'] !== null) ? intval($data['parent_id']) : null;
        
        $wpdb->insert(
            $table_name,
            array(
                'category_name' => sanitize_text_field($data['category_name']),
                'category_description' => wp_kses_post($data['category_description'] ?? ''),
                'category_slug' => sanitize_title($data['category_slug']),
                'parent_id' => $parent_id_value,
                'sort_order' => intval($data['sort_order'] ?? 0)
            ),
            array('%s', '%s', '%s', $parent_id_value ? '%d' : '%s', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update category
     */
    public static function update_category($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['category_name'])) {
            $update_data['category_name'] = sanitize_text_field($data['category_name']);
            $update_format[] = '%s';
        }
        
        if (isset($data['category_description'])) {
            $update_data['category_description'] = wp_kses_post($data['category_description']);
            $update_format[] = '%s';
        }
        
        if (isset($data['category_slug'])) {
            // Check if slug is unique
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE category_slug = %s AND id != %d",
                sanitize_title($data['category_slug']),
                $id
            ));
            
            if (!$existing) {
                $update_data['category_slug'] = sanitize_title($data['category_slug']);
                $update_format[] = '%s';
            }
        }
        
        if (isset($data['parent_id'])) {
            $parent_id_value = ($data['parent_id'] === '' || $data['parent_id'] === null) ? null : intval($data['parent_id']);
            
            // Prevent setting a category as its own parent
            if ($parent_id_value == $id) {
                return false;
            }
            
            // Prevent setting a descendant as parent (circular reference)
            if ($parent_id_value) {
                $descendants = self::get_category_descendants($id);
                $descendant_ids = array_column($descendants, 'id');
                if (in_array($parent_id_value, $descendant_ids)) {
                    return false;
                }
            }
            
            $update_data['parent_id'] = $parent_id_value;
            $update_format[] = $parent_id_value ? '%d' : '%s'; // NULL needs %s format
        }
        
        if (isset($data['sort_order'])) {
            $update_data['sort_order'] = intval($data['sort_order']);
            $update_format[] = '%d';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
    }
    
    /**
     * Delete category
     */
    public static function delete_category($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_CATEGORIES_TABLE_NAME;
        $products_table = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        // Get all descendants
        $descendants = self::get_category_descendants($id);
        $descendant_ids = array_column($descendants, 'id');
        $all_ids_to_delete = array_merge(array($id), $descendant_ids);
        
        // Set products' category_id to NULL for all categories being deleted
        foreach ($all_ids_to_delete as $cat_id) {
            $wpdb->update(
                $products_table,
                array('category_id' => null),
                array('category_id' => $cat_id),
                array('%d'),
                array('%d')
            );
        }
        
        // Delete all child categories first
        foreach ($descendant_ids as $descendant_id) {
            $wpdb->delete($table_name, array('id' => $descendant_id), array('%d'));
        }
        
        // Delete the category itself
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }
    
    /**
     * Get product count by category
     */
    public static function get_category_product_count($category_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . B2B_PRODUCTS_TABLE_NAME;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE category_id = %d",
            $category_id
        ));
    }
}

