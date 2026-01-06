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
            <tr>
                <th scope="row">
                    <label for="products_page_id">产品展示页面</label>
                </th>
                <td>
                    <?php
                    $pages = get_pages(array('post_status' => 'publish', 'sort_column' => 'post_title', 'sort_order' => 'ASC'));
                    ?>
                    <select name="products_page_id" id="products_page_id" class="regular-text">
                        <option value="0">-- 请选择页面 --</option>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($products_page_id, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">选择包含 [b2b_products] 简码的页面。此页面URL将用于分类链接和产品详情链接。</p>
                    <?php if ($products_page_id > 0): 
                        $selected_page = get_post($products_page_id);
                        if ($selected_page):
                    ?>
                        <p class="description" style="margin-top: 5px;">
                            <strong>当前选择：</strong> 
                            <a href="<?php echo esc_url(get_permalink($products_page_id)); ?>" target="_blank">
                                <?php echo esc_html($selected_page->post_title); ?>
                            </a>
                        </p>
                    <?php 
                        endif;
                    endif; 
                    ?>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="b2b_settings_submit" class="button button-primary" value="保存设置">
        </p>
    </form>
    
    <hr>
    
    <h2>使用说明</h2>
    
    <div class="card" style="margin-bottom: 20px;">
        <h3 style="margin-top: 0;">1. 产品列表短代码 [b2b_products]</h3>
        <p>在文章或页面中使用以下短代码来显示产品列表：</p>
        
        <h4>基本用法：</h4>
        <code>[b2b_products]</code>
        <p class="description">显示所有产品，默认3列，启用分页和分类筛选器</p>
        
        <h4>可用参数：</h4>
        <table class="widefat" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th style="width: 150px;">参数</th>
                    <th>类型</th>
                    <th>默认值</th>
                    <th>说明</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>columns</code></td>
                    <td>数字</td>
                    <td>3</td>
                    <td>每行显示的产品数量（1-4）</td>
                </tr>
                <tr>
                    <td><code>limit</code></td>
                    <td>数字</td>
                    <td>-1</td>
                    <td>显示的产品数量限制（-1表示全部，仅在 pagination="no" 时生效）</td>
                </tr>
                <tr>
                    <td><code>category</code></td>
                    <td>字符串</td>
                    <td>''</td>
                    <td>按分类筛选（分类 slug 或 ID）</td>
                </tr>
                <tr>
                    <td><code>category_id</code></td>
                    <td>数字</td>
                    <td>''</td>
                    <td>按分类ID筛选（明确指定分类ID）</td>
                </tr>
                <tr>
                    <td><code>per_page</code></td>
                    <td>数字</td>
                    <td>12</td>
                    <td>分页时每页显示的产品数量</td>
                </tr>
                <tr>
                    <td><code>pagination</code></td>
                    <td>字符串</td>
                    <td>yes</td>
                    <td>是否启用分页（yes/no/true/false/1/0）</td>
                </tr>
                <tr>
                    <td><code>show_filters</code></td>
                    <td>字符串</td>
                    <td>yes</td>
                    <td>是否显示分类筛选器（yes/no/true/false/1/0）</td>
                </tr>
            </tbody>
        </table>
        
        <h4>使用示例：</h4>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li><strong>自定义列数：</strong><br>
                <code>[b2b_products columns="4"]</code> - 每行显示4个产品</li>
            <li><strong>限制显示数量（不分页）：</strong><br>
                <code>[b2b_products limit="6" pagination="no"]</code> - 显示6个产品，不分页</li>
            <li><strong>启用分页：</strong><br>
                <code>[b2b_products pagination="yes" per_page="9"]</code> - 每页显示9个产品</li>
            <li><strong>按分类筛选：</strong><br>
                <code>[b2b_products category="electronics"]</code> - 显示指定分类的产品<br>
                <code>[b2b_products category_id="5"]</code> - 显示指定分类ID的产品</li>
            <li><strong>禁用分类筛选器：</strong><br>
                <code>[b2b_products show_filters="no"]</code> - 隐藏分类筛选器</li>
            <li><strong>组合使用：</strong><br>
                <code>[b2b_products category="electronics" columns="3" per_page="9" pagination="yes"]</code> - 显示指定分类，3列，每页9个，启用分页</li>
        </ul>
    </div>
    
    <div class="card">
        <h3 style="margin-top: 0;">2. 分类树短代码 [b2b_products_categories]</h3>
        <p>在前台显示树状产品分类列表：</p>
        
        <h4>基本用法：</h4>
        <code>[b2b_products_categories]</code>
        <p class="description">显示分类树，默认显示产品数量，不显示描述，不展开</p>
        
        <h4>可用参数：</h4>
        <table class="widefat" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th style="width: 150px;">参数</th>
                    <th>类型</th>
                    <th>默认值</th>
                    <th>说明</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>show_count</code></td>
                    <td>字符串</td>
                    <td>yes</td>
                    <td>是否显示每个分类下的产品数量（yes/no）</td>
                </tr>
                <tr>
                    <td><code>show_description</code></td>
                    <td>字符串</td>
                    <td>no</td>
                    <td>是否显示分类描述（yes/no）</td>
                </tr>
                <tr>
                    <td><code>expand_all</code></td>
                    <td>字符串</td>
                    <td>no</td>
                    <td>是否默认展开所有分类（yes/no）</td>
                </tr>
                <tr>
                    <td><code>link_to_products</code></td>
                    <td>字符串</td>
                    <td>yes</td>
                    <td>分类名称是否链接到产品页面（yes/no）</td>
                </tr>
            </tbody>
        </table>
        
        <h4>使用示例：</h4>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li><strong>显示产品数量：</strong><br>
                <code>[b2b_products_categories show_count="yes"]</code> - 显示每个分类的产品数量</li>
            <li><strong>显示分类描述：</strong><br>
                <code>[b2b_products_categories show_description="yes"]</code> - 显示分类的详细描述</li>
            <li><strong>默认展开所有分类：</strong><br>
                <code>[b2b_products_categories expand_all="yes"]</code> - 页面加载时自动展开所有子分类</li>
            <li><strong>不链接到产品页面：</strong><br>
                <code>[b2b_products_categories link_to_products="no"]</code> - 分类名称不显示为链接</li>
            <li><strong>组合使用：</strong><br>
                <code>[b2b_products_categories show_count="yes" show_description="yes" expand_all="yes"]</code> - 显示数量和描述，默认展开所有分类</li>
        </ul>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h3 style="margin-top: 0;">3. 搜索功能</h3>
        <p>B2B 产品已自动集成到 WordPress 默认搜索功能中。用户在前台搜索时，会自动搜索：</p>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li>产品名称</li>
            <li>产品介绍</li>
            <li>产品卖点</li>
        </ul>
        <p class="description">搜索结果会与文章、页面等一起显示，点击产品结果会跳转到产品详情页。</p>
    </div>
</div>

