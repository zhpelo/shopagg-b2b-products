<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($category);
$category_name = $is_edit ? $category['category_name'] : '';
$category_description = $is_edit ? $category['category_description'] : '';
$category_slug = $is_edit ? $category['category_slug'] : '';
$parent_id = $is_edit ? (isset($category['parent_id']) ? $category['parent_id'] : null) : null;
$sort_order = $is_edit ? $category['sort_order'] : 0;

// Get all categories for parent selection (exclude current category if editing)
$all_categories = B2B_Products_Database::get_all_categories();
$category_tree = B2B_Products_Database::get_category_tree();
?>

<div class="wrap">
    <h1><?php echo $is_edit ? '编辑分类' : '添加新分类'; ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('b2b_category_action', 'b2b_category_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="category_name">分类名称<span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" name="category_name" id="category_name" class="regular-text" value="<?php echo esc_attr($category_name); ?>" required>
                    <p class="description">分类名称（前后台统一显示）</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="category_slug">分类别名</label>
                </th>
                <td>
                    <input type="text" name="category_slug" id="category_slug" class="regular-text" value="<?php echo esc_attr($category_slug); ?>">
                    <p class="description">用于URL的别名（如果不填写，将根据分类名称自动生成）</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="parent_id">父分类</label>
                </th>
                <td>
                    <select name="parent_id" id="parent_id" class="regular-text">
                        <option value="">无（顶级分类）</option>
                        <?php
                        // Helper function to render category options recursively
                        if (!function_exists('render_category_options')) {
                            function render_category_options($categories, $parent_id, $current_id = null, $level = 0) {
                                foreach ($categories as $cat) {
                                    // Skip current category and its descendants
                                    if ($cat['id'] == $current_id) {
                                        continue;
                                    }
                                    
                                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                    $selected = ($cat['id'] == $parent_id) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($cat['id']) . '" ' . $selected . '>' . $indent . esc_html($cat['category_name']) . '</option>';
                                    
                                    // Render children recursively
                                    if (!empty($cat['children'])) {
                                        render_category_options($cat['children'], $parent_id, $current_id, $level + 1);
                                    }
                                }
                            }
                        }
                        
                        render_category_options($category_tree, $parent_id, $is_edit ? $category['id'] : null);
                        ?>
                    </select>
                    <p class="description">选择父分类以创建子分类。留空则创建顶级分类。</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="category_description">分类描述</label>
                </th>
                <td>
                    <?php
                    wp_editor($category_description, 'category_description', array(
                        'textarea_name' => 'category_description',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny' => true
                    ));
                    ?>
                    <p class="description">分类的详细描述（可选）</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="sort_order">排序</label>
                </th>
                <td>
                    <input type="number" name="sort_order" id="sort_order" class="small-text" value="<?php echo esc_attr($sort_order); ?>" min="0">
                    <p class="description">数字越小越靠前，默认为0</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="b2b_category_submit" class="button button-primary" value="<?php echo $is_edit ? '更新分类' : '添加分类'; ?>">
            <a href="<?php echo admin_url('admin.php?page=b2b-products-categories'); ?>" class="button">取消</a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-generate slug from category name
    $('#category_name').on('blur', function() {
        if (!$('#category_slug').val() || !$('#category_slug').data('manual')) {
            var slug = $(this).val().toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            $('#category_slug').val(slug);
        }
    });
    
    // Mark slug as manually edited
    $('#category_slug').on('input', function() {
        $(this).data('manual', true);
    });
});
</script>

<style>
.required {
    color: #dc3232;
}
</style>

