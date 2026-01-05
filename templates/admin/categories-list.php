<?php
if (!defined('ABSPATH')) {
    exit;
}

// Helper function to render category rows recursively
if (!function_exists('render_category_rows')) {
    function render_category_rows($categories, $categories_map, $level = 0) {
        foreach ($categories as $category) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $has_children = !empty($category['children']);
            $product_count = isset($categories_map[$category['id']]['product_count']) ? $categories_map[$category['id']]['product_count'] : 0;
            ?>
            <tr>
                <td><?php echo esc_html($category['id']); ?></td>
                <td>
                    <?php echo $indent; ?>
                    <?php if ($has_children): ?>
                        <span class="dashicons dashicons-arrow-right" style="font-size: 16px; vertical-align: middle;"></span>
                    <?php endif; ?>
                    <strong><?php echo esc_html($category['category_name']); ?></strong>
                </td>
                <td><code><?php echo esc_html($category['category_slug']); ?></code></td>
                <td><?php echo esc_html($category['sort_order']); ?></td>
                <td><?php echo esc_html($product_count); ?></td>
                <td>
                    <a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=edit&id=' . $category['id']); ?>" class="button button-small">编辑</a>
                    <a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=delete&id=' . $category['id']); ?>" 
                       class="button button-small button-link-delete" 
                       onclick="return confirm('确定要删除这个分类吗？<?php echo $has_children ? '删除后该分类及其所有子分类将被删除，' : ''; ?>该分类下的产品将变为未分类状态。');">删除</a>
                </td>
            </tr>
            <?php
            // Render children recursively
            if ($has_children) {
                render_category_rows($category['children'], $categories_map, $level + 1);
            }
        }
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">产品分类</h1>
    <a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=add'); ?>" class="page-title-action">添加新分类</a>
    <hr class="wp-header-end">
    
    <?php if (empty($category_tree)): ?>
        <p>暂无分类，<a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=add'); ?>">添加第一个分类</a></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" style="width: 80px;">ID</th>
                    <th scope="col">分类名称</th>
                    <th scope="col">分类别名</th>
                    <th scope="col" style="width: 100px;">排序</th>
                    <th scope="col" style="width: 100px;">产品数量</th>
                    <th scope="col" style="width: 180px;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php render_category_rows($category_tree, $categories_map); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

