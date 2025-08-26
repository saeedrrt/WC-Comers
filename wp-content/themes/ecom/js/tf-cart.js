jQuery(function($) {
    
    $('body').on('click', '.qty-btn', function(e) {
        e.preventDefault();

        var $btn       = $(this);
        var $row       = $btn.closest('tr.tf-cart_item');
        var cartKey    = $row.data('cart_item_key');
        var $display   = $row.find('.quantity-display');
        var currentQty = parseInt($display.text()) || 0;
        var action     = $btn.data('action');
        var newQty     = (action === 'increase') ? currentQty + 1 : currentQty - 1;

        if (newQty < 1) {
            return; // ما ينزلش للـ صفر
        }

        // طلب AJAX لتحديث الكمية
        $.post(
            wc_row_ajax.ajax_url,
            {
                action: 'tf_update_cart_row_qty',
                cart_key: cartKey,
                quantity: newQty,
                security: wc_row_ajax.nonce
            },
            function(resp) {
                if (resp.success) {
                    // حدّث العرض
                    $display.text(newQty);
                    // حدّث السعر الجزئي للصف
                    $row.find('.item-price').html(resp.data.row_total);
          
                    // لو حابب تحدّث سب‌توتال أو توتال عام:
                    $('.cart-subtotal').html(resp.data.cart_subtotal);
                    $('.cart-total').html(resp.data.cart_total);
                    $('.cart-subtotal-mer').html(resp.data.cart_subtotal);
                    $('.cart-total-mer').html(resp.data.cart_subtotal);

                } else {
                    alert(resp.data || 'خطأ في تحديث الكمية');
                }
            }
        );
    });

});



