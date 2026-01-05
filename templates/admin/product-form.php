<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($product);
$product_name = $is_edit ? (isset($product['product_name_en']) ? $product['product_name_en'] : $product['product_name']) : '';
$product_description = $is_edit ? (isset($product['product_description_en']) ? $product['product_description_en'] : (isset($product['product_description']) ? $product['product_description'] : '')) : '';
$product_highlights = $is_edit && isset($product['product_highlights']) ? $product['product_highlights'] : '';
$product_images = $is_edit && !empty($product['product_images']) ? $product['product_images'] : array();
if (!is_array($product_images)) {
    $product_images = array();
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? '编辑产品' : '添加新产品'; ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('b2b_product_action', 'b2b_product_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="product_category">产品分类</label>
                </th>
                <td>
                    <?php
                    $category_tree = B2B_Products_Database::get_category_tree();
                    $current_category_id = $is_edit && isset($product['category_id']) ? $product['category_id'] : 0;
                    
                    // Helper function to render category options recursively
                    if (!function_exists('render_product_category_options')) {
                        function render_product_category_options($categories, $current_category_id, $level = 0) {
                            foreach ($categories as $cat) {
                                $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                $selected = ($cat['id'] == $current_category_id) ? 'selected' : '';
                                echo '<option value="' . esc_attr($cat['id']) . '" ' . $selected . '>' . $indent . esc_html($cat['category_name']) . '</option>';
                                
                                // Render children recursively
                                if (!empty($cat['children'])) {
                                    render_product_category_options($cat['children'], $current_category_id, $level + 1);
                                }
                            }
                        }
                    }
                    ?>
                    <select name="category_id" id="product_category" class="regular-text">
                        <option value="">-- 未分类 --</option>
                        <?php render_product_category_options($category_tree, $current_category_id); ?>
                    </select>
                    <p class="description">选择产品所属的分类（显示分类层级关系）</p>
                    <p><a href="<?php echo admin_url('admin.php?page=b2b-products-categories&action=add'); ?>" target="_blank">添加新分类</a></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="product_name">产品名称<span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" name="product_name" id="product_name" class="regular-text" value="<?php echo esc_attr($product_name); ?>" required>
                    <p class="description">产品名称（前台显示）</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="product_highlights">产品卖点</label>
                </th>
                <td>
                    <textarea name="product_highlights" id="product_highlights" class="large-text" rows="3" maxlength="200" placeholder="请输入产品卖点，最多200个字符"><?php echo esc_textarea($product_highlights); ?></textarea>
                    <p class="description">
                        产品卖点（纯文字，最多200个字符）
                        <span id="highlights-char-count" style="color: #666; margin-left: 10px;">
                            <?php echo mb_strlen($product_highlights); ?>/200
                        </span>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="product_description">产品介绍</label>
                </th>
                <td>
                    <?php
                    wp_editor($product_description, 'product_description', array(
                        'textarea_name' => 'product_description',
                        'textarea_rows' => 15,
                        'media_buttons' => true,
                        'teeny' => false,
                        'quicktags' => true
                    ));
                    ?>
                    <p class="description">产品介绍（前台显示），可以点击"添加媒体"按钮插入图片，也可以使用工具栏格式化文本</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="product_images">产品图片</label>
                </th>
                <td>
                    <div id="product-images-container">
                        <input type="hidden" name="product_images" id="product_images" value="<?php echo esc_attr(implode(',', array_filter($product_images))); ?>">
                        <div id="product-images-preview" class="product-images-preview">
                            <?php if (!empty($product_images)): ?>
                                <?php foreach ($product_images as $image_id): 
                                    if (!$image_id) continue;
                                    $image_url = wp_get_attachment_image_url($image_id, 'medium');
                                ?>
                                    <div class="product-image-item" data-id="<?php echo esc_attr($image_id); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="">
                                        <button type="button" class="remove-image" title="删除">×</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="upload-product-images">选择图片</button>
                        <p class="description">可以上传多张产品图片</p>
                    </div>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="b2b_product_submit" class="button button-primary" value="<?php echo $is_edit ? '更新产品' : '添加产品'; ?>">
            <a href="<?php echo admin_url('admin.php?page=b2b-products'); ?>" class="button">取消</a>
        </p>
    </form>
</div>

<style>
.product-images-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}

.product-image-item {
    position: relative;
    width: 150px;
    height: 150px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.product-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-item .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3232;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image-item .remove-image:hover {
    background: #a00;
}

.required {
    color: #dc3232;
}

#highlights-char-count {
    font-weight: 500;
}

#highlights-char-count.warning {
    color: #d63638;
}

#highlights-char-count.error {
    color: #dc3232;
    font-weight: bold;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Character counter for product highlights
    var $highlightsField = $('#product_highlights');
    var $charCount = $('#highlights-char-count');
    
    function updateCharCount() {
        var text = $highlightsField.val();
        var length = text.length;
        var maxLength = 200;
        
        $charCount.text(length + '/' + maxLength);
        $charCount.removeClass('warning error');
        
        if (length > maxLength * 0.9) {
            $charCount.addClass('warning');
        }
        if (length >= maxLength) {
            $charCount.addClass('error');
            // Truncate if exceeds limit
            if (length > maxLength) {
                $highlightsField.val(text.substring(0, maxLength));
                $charCount.text(maxLength + '/' + maxLength);
            }
        }
    }
    
    $highlightsField.on('input paste', function() {
        setTimeout(updateCharCount, 10);
    });
    
    // Prevent paste of HTML content
    $highlightsField.on('paste', function(e) {
        var paste = (e.originalEvent || e).clipboardData.getData('text/plain');
        e.preventDefault();
        var currentText = $(this).val();
        var selectionStart = this.selectionStart;
        var selectionEnd = this.selectionEnd;
        var newText = currentText.substring(0, selectionStart) + paste + currentText.substring(selectionEnd);
        // Limit to 200 characters
        if (newText.length > 200) {
            newText = newText.substring(0, 200);
        }
        $(this).val(newText);
        updateCharCount();
        this.setSelectionRange(selectionStart + paste.length, selectionStart + paste.length);
    });
    
    // Initial count
    updateCharCount();
});
</script>

