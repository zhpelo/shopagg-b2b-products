<?php
if (!defined('ABSPATH')) {
    exit;
}

$columns = isset($atts['columns']) ? intval($atts['columns']) : 3;
$column_class = 'b2b-products-columns-' . $columns;
// Get show_filters from atts (passed from shortcode) or use default true
$show_filters = isset($atts['show_filters']) ? (bool)$atts['show_filters'] : (isset($show_filters) ? $show_filters : true);
$categories = isset($categories) ? $categories : array();
$current_category_slug = isset($atts['category']) ? $atts['category'] : '';
$category_locked = isset($atts['category_locked']) ? $atts['category_locked'] : false;
$pagination_enabled = isset($atts['pagination_enabled']) ? $atts['pagination_enabled'] : false;
$current_page = isset($atts['current_page']) ? intval($atts['current_page']) : 1;
$total_pages = isset($atts['total_pages']) ? intval($atts['total_pages']) : 0;
$total_count = isset($atts['total_count']) ? intval($atts['total_count']) : 0;
$per_page = isset($atts['per_page']) ? intval($atts['per_page']) : 0;
$inquiry_url = isset($inquiry_url) ? $inquiry_url : get_option('b2b_products_inquiry_url', '#contact');
$inquiry_button_text = isset($inquiry_button_text) ? $inquiry_button_text : get_option('b2b_products_inquiry_button_text', 'Request Quote');
$current_category_id = null;
if ($current_category_slug) {
    $cat = B2B_Products_Database::get_category_by_slug($current_category_slug);
    if ($cat) {
        $current_category_id = $cat['id'];
    }
}

// Build categories map for products
$categories_map = array();
foreach ($categories as $cat) {
    $categories_map[$cat['id']] = $cat;
}
?>

<div class="b2b-products-wrapper <?php echo esc_attr($column_class); ?>">
    <?php if ($show_filters && !empty($categories)): ?>
        <div class="b2b-products-filters">
            <ul class="b2b-category-filters">
                <?php
                // Remove both category and b2b_page when switching categories
                $current_url = remove_query_arg(array('category', 'b2b_page'));
                ?>
                <li class="b2b-filter-item <?php echo empty($current_category_slug) ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url($current_url); ?>" data-category="" class="b2b-filter-link">All Products</a>
                </li>
                <?php foreach ($categories as $category): ?>
                    <li class="b2b-filter-item <?php echo $current_category_id == $category['id'] ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url(add_query_arg('category', $category['category_slug'], $current_url)); ?>" data-category="<?php echo esc_attr($category['category_slug']); ?>" class="b2b-filter-link">
                            <?php echo esc_html($category['category_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (empty($products)): ?>
        <p class="b2b-products-empty">No products available<?php echo $current_category_id ? ' in this category' : ''; ?>.</p>
    <?php else: ?>
        <div class="b2b-products-grid" data-category="<?php echo esc_attr($current_category_slug); ?>">
            <?php foreach ($products as $product): 
                $images = maybe_unserialize($product['product_images']);
                if (!is_array($images)) {
                    $images = array();
                }
                $images = array_filter($images);
                $product_category = isset($product['category_id']) && isset($categories_map[$product['category_id']]) 
                    ? $categories_map[$product['category_id']] 
                    : null;
                $product_url = B2B_Products_Frontend::get_product_url($product['id']);
            ?>
                <div class="b2b-product-item" data-category-id="<?php echo isset($product['category_id']) ? esc_attr($product['category_id']) : ''; ?>">
                    <div class="b2b-product-images">
                        <?php if (!empty($images)): ?>
                            <div class="b2b-product-slider">
                                <?php foreach ($images as $index => $image_id): 
                                    $image_url = wp_get_attachment_image_url($image_id, 'large');
                                ?>
                                    <div class="b2b-product-image <?php echo $index === 0 ? 'active' : ''; ?>">
                                       <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product['product_name']); ?>">
                                      
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($images) > 1): ?>
                                    <div class="b2b-product-slider-nav">
                                        <button class="b2b-slider-prev">‹</button>
                                        <button class="b2b-slider-next">›</button>
                                    </div>
                                    <div class="b2b-product-slider-dots">
                                        <?php foreach ($images as $index => $image_id): ?>
                                            <span class="b2b-slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="b2b-product-image-placeholder">
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="b2b-product-content">
                        <h3 class="b2b-product-title">
                            <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product['product_name']); ?></a>
                        </h3>
                        <div class="b2b-product-actions">
                            <a href="<?php echo esc_url($inquiry_url); ?>" class="b2b-product-inquiry-btn">
                                <?php echo esc_html($inquiry_button_text); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($pagination_enabled && $total_pages > 1): ?>
            <div class="b2b-products-pagination">
                <?php
                // Base URL for pagination links - preserve current page URL parameters
                $base_url = remove_query_arg('b2b_page');
                // If category is in URL, preserve it; otherwise use from shortcode
                if (!$current_category_slug && isset($_GET['category'])) {
                    $base_url = add_query_arg('category', sanitize_text_field($_GET['category']), $base_url);
                } elseif ($current_category_slug) {
                    $base_url = add_query_arg('category', $current_category_slug, remove_query_arg('category', $base_url));
                }
                
                // Previous page
                if ($current_page > 1):
                    $prev_url = $current_page == 2 ? remove_query_arg('b2b_page', $base_url) : add_query_arg('b2b_page', $current_page - 1, $base_url);
                ?>
                    <a href="<?php echo esc_url($prev_url); ?>" class="b2b-pagination-link b2b-pagination-prev">
                        ‹ Previous
                    </a>
                <?php endif; ?>
                
                <div class="b2b-pagination-numbers">
                    <?php
                    // Calculate pagination range
                    $range = 2; // Show 2 pages before and after current page
                    $start = max(1, $current_page - $range);
                    $end = min($total_pages, $current_page + $range);
                    
                    // First page
                    if ($start > 1):
                    ?>
                        <a href="<?php echo esc_url($base_url); ?>" class="b2b-pagination-link <?php echo $current_page == 1 ? 'active' : ''; ?>">1</a>
                        <?php if ($start > 2): ?>
                            <span class="b2b-pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php
                    // Page numbers
                    for ($i = $start; $i <= $end; $i++):
                        $page_url = $i == 1 ? remove_query_arg('b2b_page', $base_url) : add_query_arg('b2b_page', $i, $base_url);
                    ?>
                        <a href="<?php echo esc_url($page_url); ?>" class="b2b-pagination-link <?php echo $current_page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php
                    // Last page
                    if ($end < $total_pages):
                    ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <span class="b2b-pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(add_query_arg('b2b_page', $total_pages, $base_url)); ?>" class="b2b-pagination-link <?php echo $current_page == $total_pages ? 'active' : ''; ?>">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php
                // Next page
                if ($current_page < $total_pages):
                    $next_url = add_query_arg('b2b_page', $current_page + 1, $base_url);
                ?>
                    <a href="<?php echo esc_url($next_url); ?>" class="b2b-pagination-link b2b-pagination-next">
                        Next ›
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="b2b-products-pagination-info">
                <?php
                $start_item = ($current_page - 1) * $per_page + 1;
                $end_item = min($current_page * $per_page, $total_count);
                ?>
                <p>Showing <?php echo $start_item; ?>-<?php echo $end_item; ?> of <?php echo $total_count; ?> products</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

