<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">产品分类</h1>
    <a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=add'); ?>" class="page-title-action">添加新分类</a>
    <hr class="wp-header-end">
    
    <?php if (empty($categories)): ?>
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
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo esc_html($category['id']); ?></td>
                        <td><strong><?php echo esc_html($category['category_name']); ?></strong></td>
                        <td><code><?php echo esc_html($category['category_slug']); ?></code></td>
                        <td><?php echo esc_html($category['sort_order']); ?></td>
                        <td><?php echo esc_html($category['product_count'] ?? 0); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=edit&id=' . $category['id']); ?>" class="button button-small">编辑</a>
                            <a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=delete&id=' . $category['id']); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('确定要删除这个分类吗？删除后该分类下的产品将变为未分类状态。');">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

