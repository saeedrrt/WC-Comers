jQuery(document).ready(function($){
    // جلب القيم الأولية من الـ localized script
    var minPrice = parseFloat(price_filter_params.min_price),
        maxPrice = parseFloat(price_filter_params.max_price),
        symbol   = price_filter_params.currency_symbol;

    // إنشاء السلايدر
    $('#price-slider').slider({
        range: true,
        min:   minPrice,
        max:   maxPrice,
        values: [ minPrice, maxPrice ],
        slide: function(evt, ui){
            $('#min-price').text(symbol + ui.values[0]);
            
            $('#max-price').text(symbol + ui.values[1]);
        },
        stop: function(evt, ui){
            filterProducts(ui.values[0], ui.values[1]);
        }
    });

    // دالة تغذية المنتجات الجديدة
    function filterProducts(min, max){
        $.ajax({
            url: price_filter_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action:    'filter_products_by_price',
                nonce:     price_filter_params.nonce,
                min_price: min,
                max_price: max
            },
            beforeSend: function(){
                $('.wrapper-shop').css('opacity', '0.5');
            },
            success: function(res){
                if(res.success){
                    $('.wrapper-shop').html(res.data.html);
                }
            },
            complete: function(){
                $('.wrapper-shop').css('opacity', '1');
            }
        });
    }
});
