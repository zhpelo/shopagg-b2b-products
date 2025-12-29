<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">所有产品</h1>
    <a href="<?php echo admin_url('admin.php?page=b2b-products-add'); ?>" class="page-title-action">添加新产品</a>
    <hr class="wp-header-end">
    
    <?php if (empty($products)): ?>
        <p>暂无产品，<a href="<?php echo admin_url('admin.php?page=b2b-products-add'); ?>">添加第一个产品</a></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" style="width: 40px;">ID</th>
                    <th scope="col" style="width: 120px;">产品图片</th>
                    <th scope="col">产品名称</th>
                    <th scope="col">产品卖点</th>
                    <th scope="col" style="width: 150px;">分类</th>
                    <th scope="col" style="width: 180px;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Get all categories for display
                $all_categories = B2B_Products_Database::get_all_categories();
                $categories_map = array();
                foreach ($all_categories as $cat) {
                    $categories_map[$cat['id']] = $cat;
                }
                
                foreach ($products as $product): 
                    $images = maybe_unserialize($product['product_images']);
                    $first_image = !empty($images) && is_array($images) ? $images[0] : '';
                    $product_name = isset($product['product_name_en']) ? $product['product_name_en'] : $product['product_name'];
                    $category = isset($product['category_id']) && $product['category_id'] && isset($categories_map[$product['category_id']]) 
                        ? $categories_map[$product['category_id']] 
                        : null;
                ?>
                    <tr>
                        <td><?php echo esc_html($product['id']); ?></td>
                        <td>
                            <?php if ($first_image): ?>
                                <?php echo wp_get_attachment_image($first_image, 'thumbnail', false, array('style' => 'max-width: 80px; height: auto;')); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image" style="font-size: 40px; color: #ccc;"></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($product_name); ?></strong></td>
                        <td><?php echo esc_html($product['product_highlights']); ?></td>
                        <td>
                            <?php if ($category): ?>
                                <?php echo esc_html($category['category_name']); ?>
                            <?php else: ?>
                                <span style="color: #999;">未分类</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=b2b-products-add&id=' . $product['id']); ?>" class="button button-small">编辑</a>
                            <a href="<?php echo admin_url('admin.php?page=b2b-products&action=delete&id=' . $product['id']); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('确定要删除这个产品吗？');">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

