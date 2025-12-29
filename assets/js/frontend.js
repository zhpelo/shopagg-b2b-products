jQuery(document).ready(function($) {
    // Category filter functionality
    $('.b2b-category-filters').on('click', '.b2b-filter-link', function(e) {
        var $link = $(this);
        var categorySlug = $link.data('category') || '';
        var href = $link.attr('href');
        
        // If href is different from current, allow default navigation
        // Otherwise prevent default and filter on client side if possible
        var currentCategory = new URLSearchParams(window.location.search).get('category') || '';
        if (categorySlug !== currentCategory && href && href !== '#') {
            // Allow navigation to happen
            return true;
        }
        
        e.preventDefault();
        var $wrapper = $link.closest('.b2b-products-wrapper');
        var $productsGrid = $wrapper.find('.b2b-products-grid');
        var $productItems = $productsGrid.find('.b2b-product-item');
        var $emptyMessage = $wrapper.find('.b2b-products-empty');
        
        // Update active filter
        $wrapper.find('.b2b-filter-item').removeClass('active');
        $link.closest('.b2b-filter-item').addClass('active');
        
        // Update URL without reload if supported
        if (window.history && window.history.pushState) {
            var url = href || window.location.pathname;
            if (categorySlug) {
                url += (url.indexOf('?') > -1 ? '&' : '?') + 'category=' + encodeURIComponent(categorySlug);
            }
            window.history.pushState({category: categorySlug}, '', url);
        }
        
        // Filter products (if all products are loaded)
        var hasAllProducts = $productItems.length > 0 && !$productsGrid.data('filtered');
        if (hasAllProducts) {
            if (categorySlug === '' || !categorySlug) {
                // Show all products
                $productItems.fadeIn(300);
                if ($emptyMessage.length) {
                    $emptyMessage.fadeOut(300);
                }
            } else {
                // Filter by category slug (if products have category slugs)
                var visibleCount = 0;
                $productItems.each(function() {
                    var $item = $(this);
                    var itemCategorySlug = $item.data('category-slug');
                    if (!itemCategorySlug) {
                        // Try to get from link or other data
                        itemCategorySlug = $item.find('[data-category-slug]').data('category-slug');
                    }
                    if (!itemCategorySlug || itemCategorySlug === categorySlug) {
                        $item.fadeIn(300);
                        visibleCount++;
                    } else {
                        $item.fadeOut(300);
                    }
                });
                
                if (visibleCount === 0) {
                    if (!$emptyMessage.length) {
                        $emptyMessage = $('<p class="b2b-products-empty">No products available in this category.</p>');
                        $productsGrid.after($emptyMessage);
                    }
                    $emptyMessage.fadeIn(300);
                } else {
                    $emptyMessage.fadeOut(300);
                }
            }
        }
    });
    
    // Handle browser back/forward buttons
    if (window.history && window.history.pushState) {
        $(window).on('popstate', function(e) {
            var state = e.originalEvent.state;
            var categorySlug = state && state.category ? state.category : '';
            var urlParams = new URLSearchParams(window.location.search);
            categorySlug = urlParams.get('category') || categorySlug || '';
            
            // Trigger filter update
            $('.b2b-category-filters').find('[data-category="' + categorySlug + '"]').first().trigger('click');
        });
    }
    
    // Product image slider functionality
    $('.b2b-product-slider').each(function() {
        var $slider = $(this);
        var $images = $slider.find('.b2b-product-image');
        var $dots = $slider.find('.b2b-slider-dot');
        var $prev = $slider.find('.b2b-slider-prev');
        var $next = $slider.find('.b2b-slider-next');
        var currentIndex = 0;
        var totalImages = $images.length;
        
        if (totalImages <= 1) {
            return; // No need for slider if only one image
        }
        
        // Show image by index
        function showImage(index) {
            $images.removeClass('active').eq(index).addClass('active');
            $dots.removeClass('active').eq(index).addClass('active');
            currentIndex = index;
        }
        
        // Next image
        $next.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var nextIndex = (currentIndex + 1) % totalImages;
            showImage(nextIndex);
        });
        
        // Previous image
        $prev.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var prevIndex = (currentIndex - 1 + totalImages) % totalImages;
            showImage(prevIndex);
        });
        
        // Dot navigation
        $dots.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var index = $(this).data('slide');
            showImage(index);
        });
        
        // Auto-play slider (optional, uncomment if needed)
        /*
        var autoPlayInterval = setInterval(function() {
            var nextIndex = (currentIndex + 1) % totalImages;
            showImage(nextIndex);
        }, 5000);
        
        $slider.on('mouseenter', function() {
            clearInterval(autoPlayInterval);
        }).on('mouseleave', function() {
            autoPlayInterval = setInterval(function() {
                var nextIndex = (currentIndex + 1) % totalImages;
                showImage(nextIndex);
            }, 5000);
        });
        */
    });
    
    // Simple lightbox functionality (if not using a plugin)
    $('.b2b-product-image-link').on('click', function(e) {
        // If you have a lightbox plugin, remove this and use the plugin's data attribute
        // This is a simple implementation
        var imageUrl = $(this).attr('href');
        if (imageUrl && imageUrl !== '#' && !imageUrl.startsWith('#')) {
            // Allow default behavior for external links or lightbox plugins
            // If you want a simple modal, uncomment below:
            /*
            e.preventDefault();
            // Simple modal implementation can be added here
            window.open(imageUrl, '_blank');
            */
        }
    });
});

