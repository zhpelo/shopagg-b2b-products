# B2B Products Showcase

一个专业的 WordPress B2B 产品展示插件，支持产品管理、分类管理、多图上传、询价功能等。

## 页面截图

<img width="2790" height="1412" alt="image" src="https://github.com/user-attachments/assets/e1f7e682-f54f-4c68-8cba-44d18f0b45ec" />

<img width="3840" height="7950" alt="image" src="https://github.com/user-attachments/assets/ef873c0c-10a0-4d8b-87c6-a208beb840e4" />

## 功能特性

### 核心功能
- ✅ **产品管理** - 添加、编辑、删除产品
- ✅ **产品分类** - 支持分类管理，产品可按分类筛选
- ✅ **多图上传** - 支持为每个产品上传多张图片
- ✅ **产品详情页** - 自动生成产品详情页，支持图片轮播
- ✅ **询价功能** - 可自定义询价按钮链接和文字
- ✅ **产品卖点** - 支持添加产品卖点（纯文字，最多200字符）
- ✅ **富文本编辑** - 产品介绍支持富文本编辑，可插入图片和格式化文本

### 展示功能
- ✅ **响应式设计** - 完美适配桌面和移动设备
- ✅ **分页功能** - 支持产品列表分页显示
- ✅ **分类筛选** - 前台可按分类快速筛选产品
- ✅ **图片轮播** - 产品列表和详情页支持图片轮播展示

### 管理功能
- ✅ **中文后台** - 后台管理界面完全中文化
- ✅ **英文前台** - 前台展示使用英文（可自定义）
- ✅ **分类管理** - 支持分类的添加、编辑、删除
- ✅ **产品搜索** - 方便管理大量产品

## 系统要求

- WordPress 5.0 或更高版本
- PHP 7.0 或更高版本
- MySQL 5.6 或更高版本

## 安装方法

### 方法一：手动安装

1. 下载插件压缩包
2. 解压文件到 `wp-content/plugins/` 目录
3. 在 WordPress 后台的"插件"页面激活插件
4. 插件会自动创建所需的数据表

### 方法二：通过 WordPress 后台上传

1. 进入 WordPress 后台 → 插件 → 添加新插件
2. 点击"上传插件"
3. 选择插件压缩包并上传
4. 激活插件

## 使用说明

### 1. 基本设置

激活插件后，首先进行基本设置：

1. 进入 **B2B产品 → 设置**
2. 设置"询价按钮链接"（如：`#contact` 或 `https://example.com/contact`）
3. 设置"询价按钮文字"（默认：`Request Quote`）
4. 点击"保存设置"

### 2. 创建分类

在添加产品前，建议先创建产品分类：

1. 进入 **B2B产品 → 产品分类**
2. 点击"添加新分类"
3. 填写分类名称、分类描述（可选）、分类别名（可选）
4. 设置排序（数字越小越靠前）
5. 点击"添加分类"

### 3. 添加产品

1. 进入 **B2B产品 → 添加产品**
2. 选择产品分类（可选）
3. 填写产品名称（必填）
4. 填写产品卖点（可选，最多200字符）
5. 使用富文本编辑器填写产品介绍（可选，可插入图片）
6. 上传产品图片（可上传多张）
7. 点击"添加产品"

### 4. 显示产品列表

在文章或页面中使用短代码显示产品：

**基本用法：**
```
[b2b_products]
```

**自定义列数：**
```
[b2b_products columns="3"]
```

**限制显示数量：**
```
[b2b_products limit="6"]
```

**启用分页：**
```
[b2b_products pagination="yes" per_page="12"]
```

**按分类筛选：**
```
[b2b_products category="electronics"]
```

## 短代码参数

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `columns` | 数字 | `3` | 每行显示的产品数量（1-4） |
| `limit` | 数字 | `-1` | 显示的产品数量限制（-1表示全部） |
| `category` | 字符串 | `''` | 按分类筛选（分类 slug 或 ID） |
| `category_id` | 数字 | `''` | 按分类ID筛选（明确指定） |
| `per_page` | 数字 | `12` | 分页时每页显示的产品数量 |
| `pagination` | 字符串 | `yes` | 是否启用分页（yes/no） |
| `show_filters` | 字符串 | `yes` | 是否显示分类筛选器（yes/no） |

### 短代码示例

**基础展示：**
```
[b2b_products]
```

**3列显示，每页6个产品：**
```
[b2b_products columns="3" per_page="6"]
```

**显示指定分类的产品：**
```
[b2b_products category="electronics" columns="4"]
```

**显示指定分类ID的产品：**
```
[b2b_products category_id="5"]
```

**禁用分类筛选器：**
```
[b2b_products show_filters="no"]
```

**禁用分页，显示所有产品：**
```
[b2b_products pagination="no"]
```

**组合使用：**
```
[b2b_products category="electronics" columns="3" per_page="9" pagination="yes"]
```

## 产品详情页

每个产品都会自动生成一个详情页，URL 格式为：
```
/product/{产品ID}/
```

例如：`/product/123/`

## 目录结构

```
b2b-products/
├── assets/
│   ├── css/
│   │   ├── admin.css          # 后台样式
│   │   └── frontend.css       # 前端样式
│   └── js/
│       ├── admin.js           # 后台脚本
│       └── frontend.js        # 前端脚本
├── includes/
│   ├── class-b2b-products-admin.php      # 管理类
│   ├── class-b2b-products-database.php   # 数据库类
│   └── class-b2b-products-frontend.php   # 前端类
├── templates/
│   ├── admin/
│   │   ├── categories-list.php    # 分类列表
│   │   ├── category-form.php      # 分类表单
│   │   ├── product-form.php       # 产品表单
│   │   ├── products-list.php      # 产品列表
│   │   └── settings.php           # 设置页面
│   └── frontend/
│       ├── product-detail.php     # 产品详情页
│       └── products-grid.php      # 产品列表模板
├── b2b-products.php               # 主插件文件
└── README.md                      # 说明文档
```

## 数据库结构

插件会创建两个数据表：

### b2b_products（产品表）
- `id` - 产品ID（主键）
- `product_name` - 产品名称
- `product_description` - 产品介绍（HTML格式）
- `product_images` - 产品图片（序列化数组）
- `category_id` - 分类ID（外键）
- `product_highlights` - 产品卖点（最多200字符）
- `created_at` - 创建时间
- `updated_at` - 更新时间

### b2b_product_categories（分类表）
- `id` - 分类ID（主键）
- `category_name` - 分类名称
- `category_description` - 分类描述
- `category_slug` - 分类别名（唯一）
- `sort_order` - 排序
- `created_at` - 创建时间
- `updated_at` - 更新时间

## 自定义样式

插件提供了丰富的 CSS 类，你可以通过主题的 `style.css` 或自定义 CSS 来自定义样式：

### 产品列表相关类
- `.b2b-products-wrapper` - 产品容器
- `.b2b-products-grid` - 产品网格
- `.b2b-product-item` - 单个产品项
- `.b2b-product-title` - 产品标题
- `.b2b-product-highlights` - 产品卖点
- `.b2b-product-description` - 产品描述

### 分类筛选相关类
- `.b2b-products-filters` - 筛选器容器
- `.b2b-category-filters` - 分类筛选列表
- `.b2b-filter-item` - 筛选项
- `.b2b-filter-link` - 筛选链接

### 分页相关类
- `.b2b-products-pagination` - 分页容器
- `.b2b-pagination-link` - 分页链接
- `.b2b-pagination-link.active` - 当前页

## 常见问题

### Q: 如何修改产品详情页的 URL 结构？
A: 插件使用 WordPress 的 rewrite rules 功能，产品详情页 URL 固定为 `/product/{ID}/`。如需修改，可以在 `class-b2b-products-frontend.php` 的 `add_rewrite_rules()` 方法中修改。

### Q: 可以在产品介绍中插入视频吗？
A: 是的，产品介绍使用 WordPress 富文本编辑器，支持插入图片、链接等。如果要插入视频，可以使用 WordPress 的 oEmbed 功能或直接使用 iframe 代码。

### Q: 如何批量导入产品？
A: 当前版本暂不支持批量导入功能。如需批量导入，建议通过数据库直接导入或开发自定义导入脚本。

### Q: 插件会影响网站性能吗？
A: 插件已进行了性能优化，使用 WordPress 标准的数据库查询和缓存机制。如果产品数量很多（超过1000个），建议启用分页功能。

## 更新日志

### 1.0.0 (2025-12-30)
- 初始版本发布
- 产品管理功能
- 分类管理功能
- 多图上传功能
- 产品详情页
- 分页功能
- 分类筛选功能
- 产品卖点功能
- 询价功能

## 支持

如有问题或建议，请联系：
- 作者：庄朋龙
- 网站：https://shopagg.com
- 插件页面：https://shopagg.com/wp-plugins/shopagg-b2b-products

## 许可证

本插件遵循 GPL v2 或更高版本许可证。

## 致谢

感谢使用 B2B Products Showcase 插件！

