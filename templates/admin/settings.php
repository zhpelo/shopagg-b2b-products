<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>B2B产品设置</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('b2b_settings_action', 'b2b_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="inquiry_url">询价按钮链接</label>
                </th>
                <td>
                    <input type="text" name="inquiry_url" id="inquiry_url" class="regular-text" value="<?php echo esc_attr($inquiry_url); ?>" placeholder="#contact 或 https://example.com/contact">
                    <p class="description">前台产品展示页面询价按钮的跳转链接。可以是内部链接（如 #contact）或外部链接（如 https://example.com/contact）</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="inquiry_button_text">询价按钮文字</label>
                </th>
                <td>
                    <input type="text" name="inquiry_button_text" id="inquiry_button_text" class="regular-text" value="<?php echo esc_attr($inquiry_button_text); ?>" placeholder="Request Quote">
                    <p class="description">前台产品展示页面询价按钮显示的文字（默认：Request Quote）</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="b2b_settings_submit" class="button button-primary" value="保存设置">
        </p>
    </form>
    
    <hr>
    
    <h2>使用说明</h2>
    <div class="card">
        <p><strong>短代码使用：</strong></p>
        <p>在文章或页面中使用以下短代码来显示产品列表：</p>
        <code>[b2b_products]</code>
        <p>或者指定列数和数量：</p>
        <code>[b2b_products columns="3" limit="6"]</code>
        <ul>
            <li><code>columns</code> - 每行显示的产品数量（默认：3）</li>
            <li><code>limit</code> - 显示的产品数量限制（默认：-1，显示全部）</li>
        </ul>

        <p>启用分页，每页显示12个产品（默认）</p>
        <code>[b2b_products pagination="yes"]</code>
        <p>启用分页，每页显示6个产品</p>
        <code>[b2b_products per_page="6"]</code>
        <p>禁用分页，显示所有产品（原有行为）</p>
        <code>[b2b_products pagination="no"]</code>
        <p>结合分类筛选和分页</p>
        <code>[b2b_products category="electronics" per_page="9" columns="3"]</code>
        <p>限制数量但不分页（limit 参数优先于 per_page，如果 pagination="no"）</p>
        <code>[b2b_products limit="6" pagination="no"]</code>
    </div>
</div>

