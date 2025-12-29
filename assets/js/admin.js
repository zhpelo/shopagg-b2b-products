jQuery(document).ready(function($) {
    var fileFrame;
    var productImages = [];
    
    // Get current images
    function updateProductImages() {
        var imagesInput = $('#product_images');
        var currentValue = imagesInput.val();
        if (currentValue) {
            productImages = currentValue.split(',').filter(function(id) {
                return id.trim() !== '';
            });
        } else {
            productImages = [];
        }
    }
    
    updateProductImages();
    
    // Upload images button
    $('#upload-product-images').on('click', function(e) {
        e.preventDefault();
        
        updateProductImages();
        
        if (fileFrame) {
            fileFrame.open();
            return;
        }
        
        fileFrame = wp.media({
            title: '选择产品图片',
            button: {
                text: '使用选中的图片'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });
        
        fileFrame.on('select', function() {
            var attachments = fileFrame.state().get('selection').toJSON();
            
            attachments.forEach(function(attachment) {
                if (productImages.indexOf(String(attachment.id)) === -1) {
                    productImages.push(String(attachment.id));
                    
                    var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                    
                    var imageHtml = '<div class="product-image-item" data-id="' + attachment.id + '">' +
                        '<img src="' + imageUrl + '" alt="">' +
                        '<button type="button" class="remove-image" title="删除">×</button>' +
                        '</div>';
                    
                    $('#product-images-preview').append(imageHtml);
                }
            });
            
            updateImagesInput();
        });
        
        fileFrame.open();
    });
    
    // Remove image
    $(document).on('click', '.remove-image', function() {
        var imageItem = $(this).closest('.product-image-item');
        var imageId = imageItem.data('id');
        
        productImages = productImages.filter(function(id) {
            return String(id) !== String(imageId);
        });
        
        imageItem.remove();
        updateImagesInput();
    });
    
    // Update hidden input
    function updateImagesInput() {
        $('#product_images').val(productImages.join(','));
    }
});

