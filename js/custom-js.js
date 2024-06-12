jQuery(document).ready(function($) {
    $('.add-to-wishlist').on('click', function(e) {
        e.preventDefault();
        var product_id = $(this).data('product-id');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_add_to_wishlist',
                product_id: product_id
            },
            success: function(response) {
                if (response.success) {
                    alert('Product added to wishlist! Total items: ' + response.data.count);
                } else {
                    alert(response.data);
                }
            }
        });
    });

    function updateWishlistCount() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_wishlist_count'
            },
            success: function(response) {
                if (response.success) {
                    $('#wishlist-count').text(response.data.count);
                }
            }
        });
    }

    updateWishlistCount();
    
});
