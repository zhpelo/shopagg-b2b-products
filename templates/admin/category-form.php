<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($category);
$category_name = $is_edit ? $category['category_name'] : '';
$category_description = $is_edit ? $category['category_description'] : '';
$category_slug = $is_edit ? $category['category_slug'] : '';
$sort_order = $is_edit ? $category['sort_order'] : 0;
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

