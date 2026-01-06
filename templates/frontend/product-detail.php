<?php

/**
 * Product Detail Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get product data
$product_id = get_query_var('b2b_product_id');
$product = B2B_Products_Database::get_product($product_id);

if (!$product) {
    // Should not reach here, but just in case
    wp_die('Product not found');
}

$product_name = isset($product['product_name_en']) ? $product['product_name_en'] : $product['product_name'];
$product_description = isset($product['product_description_en']) ? $product['product_description_en'] : (isset($product['product_description']) ? $product['product_description'] : '');
$images = maybe_unserialize($product['product_images']);
if (!is_array($images)) {
    $images = array();
}
$images = array_filter($images);
$inquiry_url = get_option('b2b_products_inquiry_url', '#contact');
$inquiry_button_text = get_option('b2b_products_inquiry_button_text', 'Request Quote');

get_header();
?>

<div class="b2b-product-detail-wrapper">
    <div class="b2b-product-detail-container">
        <div class="b2b-product-detail-images">
            <?php if (!empty($images)): ?>
                <div class="b2b-product-detail-slider">
                    <div class="b2b-product-detail-main-image">
                        <?php
                        $main_image_url = wp_get_attachment_image_url($images[0], 'large');
                        $main_full_url = wp_get_attachment_image_url($images[0], 'full');
                        ?>
                        <?php if (count($images) > 1): ?>
                            <button type="button" class="b2b-product-nav-btn b2b-product-nav-prev" aria-label="上一张">
                                <span>‹</span>
                            </button>
                            <button type="button" class="b2b-product-nav-btn b2b-product-nav-next" aria-label="下一张">
                                <span>›</span>
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($main_full_url); ?>" class="b2b-product-image-link" id="b2b-main-product-link" data-lightbox="product-detail-<?php echo esc_attr($product['id']); ?>">
                            <img src="<?php echo esc_url($main_image_url); ?>" alt="<?php echo esc_attr($product_name); ?>" id="b2b-main-product-image">
                        </a>
                    </div>

                    <?php if (count($images) > 1): ?>
                        <div class="b2b-product-detail-thumbnails">
                            <?php foreach ($images as $index => $image_id):
                                $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                $large_url = wp_get_attachment_image_url($image_id, 'large');
                                $full_url = wp_get_attachment_image_url($image_id, 'full');
                            ?>
                                <div class="b2b-product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-image-index="<?php echo $index; ?>">
                                    <a href="<?php echo esc_url($full_url); ?>" class="b2b-product-image-link" data-lightbox="product-detail-<?php echo esc_attr($product['id']); ?>">
                                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($product_name); ?>" data-large="<?php echo esc_url($large_url); ?>" data-full="<?php echo esc_url($full_url); ?>">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="b2b-product-image-placeholder">
                    <span>No Image Available</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="b2b-product-detail-content">
            <?php
            // Get product category
            $product_category = null;
            if (isset($product['category_id']) && $product['category_id']) {
                $product_category = B2B_Products_Database::get_category($product['category_id']);
            }
            ?>

            <?php if ($product_category): ?>
                <div class="b2b-product-category">
                    <a href="<?php echo esc_url(add_query_arg('category', $product_category['category_slug'], home_url('/'))); ?>">
                        <?php echo esc_html($product_category['category_name']); ?>
                    </a>
                </div>
            <?php endif; ?>

            <h1 class="b2b-product-detail-title"><?php echo esc_html($product_name); ?></h1>
            
            <?php if (!empty($product['product_highlights'])): ?>
                <div class="b2b-product-detail-highlights">
                    <?php echo esc_html($product['product_highlights']); ?>
                </div>
            <?php endif; ?>
            
            <div class="b2b-product-detail-actions">
                <a href="<?php echo esc_url($inquiry_url); ?>" class="b2b-product-inquiry-btn">
                    <?php echo esc_html($inquiry_button_text); ?>
                </a>
            </div>
        </div>
    </div>


    <?php if (!empty($product_description)): ?>
        <div class="b2b-product-detail-description">
            <?php echo wp_kses_post(wpautop($product_description)); ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .b2b-product-detail-wrapper {
        max-width: 1280px;
        margin: 40px auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    .b2b-product-detail-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: start;
        width: 100%;
    }

    .b2b-product-detail-images {
        position: sticky;
        top: 20px;
        width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .b2b-product-detail-content {
        width: 100%;
        min-width: 0;
    }

    .b2b-product-detail-main-image {
        position: relative;
        margin-bottom: 20px;
    }

    .b2b-product-detail-main-image img {
        width: 100%;
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        display: block;
        box-sizing: border-box;
    }

    .b2b-product-detail-main-image .b2b-product-image-link {
        display: block;
    }

    .b2b-product-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.9);
        border: none;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        font-size: 24px;
        color: #333;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .b2b-product-nav-btn:hover {
        background: rgba(255, 255, 255, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: #0073aa;
    }

    .b2b-product-nav-btn:active {
        transform: translateY(-50%) scale(0.95);
    }

    .b2b-product-nav-prev {
        left: 15px;
    }

    .b2b-product-nav-next {
        right: 15px;
    }

    .b2b-product-detail-thumbnails {
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: thin;
        scrollbar-color: #ccc transparent;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 5px;
    }

    .b2b-product-detail-thumbnails::-webkit-scrollbar {
        height: 6px;
    }

    .b2b-product-detail-thumbnails::-webkit-scrollbar-track {
        background: transparent;
    }

    .b2b-product-detail-thumbnails::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }

    .b2b-product-detail-thumbnails::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    .b2b-product-thumbnail {
        width: 100px;
        min-width: 100px;
        height: 100px;
        min-height: 100px;
        border: 2px solid transparent;
        border-radius: 4px;
        overflow: hidden;
        cursor: pointer;
        transition: border-color 0.3s;
    }

    .b2b-product-thumbnail.active {
        border-color: #0073aa;
    }

    .b2b-product-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .b2b-product-category {
        margin-bottom: 15px;
        font-size: 14px;
    }

    .b2b-product-category a {
        display: inline-block;
        padding: 6px 12px;
        background: #f5f5f5;
        color: #0073aa;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .b2b-product-category a:hover {
        background: #0073aa;
        color: #fff;
    }

    .b2b-product-detail-title {
        font-size: 32px;
        font-weight: 600;
        margin: 0 0 20px 0;
        color: #333;
    }

    .b2b-product-detail-highlights {
        font-size: 16px;
        line-height: 1.8;
        color: #0073aa;
        font-weight: 500;
        margin-bottom: 25px;
        padding: 15px 20px;
        background: #f0f8ff;
        border-left: 4px solid #0073aa;
        border-radius: 4px;
    }

    .b2b-product-detail-description {
        margin: 50px 0;
        padding: 50px;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        background-color: #fff;
        font-size: 16px;
        line-height: 1.8;
        margin-bottom: 30px;
    }

    .b2b-product-detail-description p {
        margin-bottom: 15px;
   
    }

    .b2b-product-detail-actions {
        margin-top: 30px;
        display: flex;
    }

    .b2b-product-view-btn {
        display: inline-block;
        padding: 12px 30px;
        background: #666;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        margin-right: 10px;
        transition: background 0.3s ease;
    }

    .b2b-product-view-btn:hover {
        background: #555;
        color: #fff;
    }

    @media (max-width: 768px) {
        .b2b-product-detail-container {
            grid-template-columns: 1fr;
        }

        .b2b-product-detail-images {
            position: static;
        }

        .b2b-product-detail-title {
            font-size: 24px;
        }

        .b2b-product-nav-btn {
            width: 36px;
            height: 36px;
            font-size: 20px;
        }

        .b2b-product-nav-prev {
            left: 10px;
        }

        .b2b-product-nav-next {
            right: 10px;
        }

        .b2b-product-thumbnail {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        var currentImageIndex = 0;
        var totalImages = $('.b2b-product-thumbnail').length;
        
        // Function to update main image
        function updateMainImage(index) {
            if (index < 0 || index >= totalImages) return;
            
            currentImageIndex = index;
            var $thumbnail = $('.b2b-product-thumbnail').eq(index);
            var $img = $thumbnail.find('img');
            var largeUrl = $img.data('large');
            var fullUrl = $img.data('full');

            // Update main image
            $('#b2b-main-product-image').attr('src', largeUrl);
            $('#b2b-main-product-link').attr('href', fullUrl);

            // Update active thumbnail
            $('.b2b-product-thumbnail').removeClass('active');
            $thumbnail.addClass('active');
            
            // Scroll thumbnail into view
            var $thumbnailsContainer = $('.b2b-product-detail-thumbnails');
            var containerWidth = $thumbnailsContainer.width();
            var thumbnailWidth = $thumbnail.outerWidth(true);
            var thumbnailOffset = $thumbnail.position().left;
            var scrollLeft = $thumbnailsContainer.scrollLeft();
            var thumbnailCenter = scrollLeft + thumbnailOffset - (containerWidth / 2) + (thumbnailWidth / 2);
            
            $thumbnailsContainer.animate({
                scrollLeft: thumbnailCenter
            }, 300);
        }
        
        // Thumbnail click handler
        $('.b2b-product-thumbnail').on('click', function(e) {
            e.preventDefault();
            var index = $(this).index();
            updateMainImage(index);
        });
        
        // Previous button handler
        $('.b2b-product-nav-prev').on('click', function(e) {
            e.preventDefault();
            var newIndex = currentImageIndex - 1;
            if (newIndex < 0) {
                newIndex = totalImages - 1; // Loop to last image
            }
            updateMainImage(newIndex);
        });
        
        // Next button handler
        $('.b2b-product-nav-next').on('click', function(e) {
            e.preventDefault();
            var newIndex = currentImageIndex + 1;
            if (newIndex >= totalImages) {
                newIndex = 0; // Loop to first image
            }
            updateMainImage(newIndex);
        });
        
        // Keyboard navigation
        $(document).on('keydown', function(e) {
            // Only navigate when not typing in input fields
            if ($(e.target).is('input, textarea')) return;
            
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                $('.b2b-product-nav-prev').trigger('click');
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                $('.b2b-product-nav-next').trigger('click');
            }
        });
    });
</script>

<?php
get_footer();
