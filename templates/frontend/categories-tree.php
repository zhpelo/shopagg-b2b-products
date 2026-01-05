<?php
if (!defined('ABSPATH')) {
    exit;
}

$show_count = isset($atts['show_count']) ? $atts['show_count'] : true;
$show_description = isset($atts['show_description']) ? $atts['show_description'] : false;
$expand_all = isset($atts['expand_all']) ? $atts['expand_all'] : false;
$link_to_products = isset($atts['link_to_products']) ? $atts['link_to_products'] : true;
$categories_map = isset($categories_map) ? $categories_map : array();
$products_page_url = isset($products_page_url) ? $products_page_url : get_permalink();
$category_tree = isset($category_tree) ? $category_tree : array();

// Helper function to render category tree
if (!function_exists('render_category_tree_item')) {
    function render_category_tree_item($category, $categories_map, $products_page_url, $show_count, $show_description, $link_to_products, $level = 0) {
        $has_children = !empty($category['children']);
        $product_count = isset($categories_map[$category['id']]) ? $categories_map[$category['id']] : 0;
        $category_url = $link_to_products ? add_query_arg('category', $category['category_slug'], $products_page_url) : '#';
        $indent_class = $level > 0 ? 'b2b-category-child b2b-category-level-' . $level : '';
        ?>
        <li class="b2b-category-item <?php echo esc_attr($indent_class); ?>" data-category-id="<?php echo esc_attr($category['id']); ?>">
            <div class="b2b-category-header">
                <?php if ($has_children): ?>
                    <button type="button" class="b2b-category-toggle" aria-expanded="false">
                        <span class="b2b-category-toggle-icon">▶</span>
                    </button>
                <?php else: ?>
                    <span class="b2b-category-spacer"></span>
                <?php endif; ?>
                
                <?php if ($link_to_products): ?>
                    <a href="<?php echo esc_url($category_url); ?>" class="b2b-category-link">
                        <span class="b2b-category-name"><?php echo esc_html($category['category_name']); ?></span>
                        <?php if ($show_count && $product_count > 0): ?>
                            <span class="b2b-category-count">(<?php echo esc_html($product_count); ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <span class="b2b-category-name">
                        <?php echo esc_html($category['category_name']); ?>
                        <?php if ($show_count && $product_count > 0): ?>
                            <span class="b2b-category-count">(<?php echo esc_html($product_count); ?>)</span>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <?php if ($show_description && !empty($category['category_description'])): ?>
                <div class="b2b-category-description">
                    <?php echo wp_kses_post($category['category_description']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($has_children): ?>
                <ul class="b2b-category-children" style="display: none;">
                    <?php foreach ($category['children'] as $child): ?>
                        <?php render_category_tree_item($child, $categories_map, $products_page_url, $show_count, $show_description, $link_to_products, $level + 1); ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php
    }
}
?>

<div class="b2b-categories-tree-wrapper">
    <?php if (empty($category_tree)): ?>
        <p class="b2b-categories-empty">No categories available.</p>
    <?php else: ?>
        <ul class="b2b-categories-tree" data-expand-all="<?php echo $expand_all ? 'true' : 'false'; ?>">
            <?php foreach ($category_tree as $category): ?>
                <?php render_category_tree_item($category, $categories_map, $products_page_url, $show_count, $show_description, $link_to_products); ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<style>
.b2b-categories-tree-wrapper {
    width: 100%;
    margin: 20px 0;
}

.b2b-categories-empty {
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 16px;
}

.b2b-categories-tree {
    list-style: none;
    padding: 0;
    margin: 0;
}

.b2b-category-item {
    margin: 0;
    padding: 0;
}

.b2b-category-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.b2b-category-item:last-child > .b2b-category-header {
    border-bottom: none;
}

.b2b-category-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    transition: transform 0.3s ease;
    color: #666;
}

.b2b-category-toggle:hover {
    color: #0073aa;
}

.b2b-category-toggle-icon {
    display: inline-block;
    transition: transform 0.3s ease;
    font-size: 12px;
}

.b2b-category-item.expanded > .b2b-category-header .b2b-category-toggle .b2b-category-toggle-icon {
    transform: rotate(90deg);
}

.b2b-category-spacer {
    width: 24px;
    display: inline-block;
}

.b2b-category-link {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
    flex: 1;
}

.b2b-category-link:hover {
    color: #0073aa;
}

.b2b-category-name {
    color: #333;
    font-weight: 500;
}

.b2b-category-count {
    color: #666;
    font-weight: normal;
    font-size: 0.9em;
    margin-left: 6px;
}

.b2b-category-description {
    padding: 8px 0 8px 32px;
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    border-bottom: 1px solid #f0f0f0;
}

.b2b-category-children {
    list-style: none;
    padding: 0;
    margin: 0;
    padding-left: 24px;
}

.b2b-category-child {
    position: relative;
}

.b2b-category-child::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: #e0e0e0;
}

.b2b-category-level-1 .b2b-category-header {
    padding-left: 12px;
}

.b2b-category-level-2 .b2b-category-header {
    padding-left: 24px;
}

.b2b-category-level-3 .b2b-category-header {
    padding-left: 36px;
}

.b2b-category-level-4 .b2b-category-header {
    padding-left: 48px;
}

/* Expand all when data-expand-all is true */
.b2b-categories-tree[data-expand-all="true"] .b2b-category-children {
    display: block !important;
}

.b2b-categories-tree[data-expand-all="true"] .b2b-category-item {
    display: block;
}

.b2b-categories-tree[data-expand-all="true"] .b2b-category-item > .b2b-category-header .b2b-category-toggle .b2b-category-toggle-icon {
    transform: rotate(90deg);
}

.b2b-categories-tree[data-expand-all="true"] .b2b-category-item > .b2b-category-header .b2b-category-toggle {
    aria-expanded: "true";
}

/* Responsive */
@media (max-width: 768px) {
    .b2b-category-header {
        padding: 10px 0;
    }
    
    .b2b-category-description {
        padding-left: 24px;
        font-size: 13px;
    }
    
    .b2b-category-children {
        padding-left: 16px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.b2b-category-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $item = $(this).closest('.b2b-category-item');
        var $children = $item.find('> .b2b-category-children');
        var isExpanded = $item.hasClass('expanded');
        
        if (isExpanded) {
            $item.removeClass('expanded');
            $children.slideUp(300);
            $(this).attr('aria-expanded', 'false');
        } else {
            $item.addClass('expanded');
            $children.slideDown(300);
            $(this).attr('aria-expanded', 'true');
        }
    });
    
    // Expand all if data-expand-all is true
    if ($('.b2b-categories-tree').data('expand-all') === true) {
        $('.b2b-category-item').addClass('expanded');
        $('.b2b-category-children').show();
        $('.b2b-category-toggle').attr('aria-expanded', 'true');
    }
});
</script>

