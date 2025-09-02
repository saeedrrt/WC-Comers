jQuery(function($) {
    // عند الضغط على كاتيجوري
    $(document).on('click', '.filter-cat', function(e) {
        e.preventDefault();
        $('.filter-cat, .filter-bar').removeClass('active');
        $(this).addClass('active');
        applyFilter();
    });

    // عند الضغط على براند (شبيه)
    $(document).on('click', '.filter-bar', function(e) {
        e.preventDefault();
        $('.filter-cat, .filter-bar').removeClass('active');
        $(this).addClass('active');
        applyFilter();
    });

    // عند تغيير حالة checkbox الخاص بالتخفيضات
    $(document).on('change', '#sale', function() {
        applyFilter();
    });

    // عند تغيير حالة radio buttons الخاصة بالتوفر
    $(document).on('change', 'input[name="availability"]', function() {
        applyFilter();
    });

    function applyFilter() {
        const categories = $('.filter-cat.active').map(function() {
            return $(this).data('cat');
        }).get();
        const brands = $('.filter-bar.active').map(function() {
            return $(this).data('bar');
        }).get();

        // التحقق من حالة checkbox التخفيضات
        const saleOnly = $('#sale').is(':checked');

        // التحقق من حالة radio buttons التوفر
        const availability = $('input[name="availability"]:checked').attr('id') || '';

        $.ajax({
            url: filter_params.ajax_url,
            type: 'POST',
            dataType: 'html',
            data: {
                action: 'filter_products',
                categories: categories,
                brands: brands,
                sale_only: saleOnly,
                availability: availability,
                nonce: filter_params.nonce
            },
            beforeSend() {
                $('.wrapper-shop').css('opacity', 0.5);
            },
            success(html) {
                $('.wrapper-shop').html(html);
            },
            complete() {
                $('.wrapper-shop').css('opacity', 1);
            }
        });

        updateFilterCounts()
    }

    function updateFilterCounts() {
        $.ajax({
            url: filter_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update_filter_counts',
                nonce: filter_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    // تحديث عدد المنتجات المتوفرة
                    $('#inStock').siblings('label').find('.count').text(response.data.instock_count);

                    // تحديث عدد المنتجات غير المتوفرة
                    $('#outStock').siblings('label').find('.count').text(response.data.outstock_count);

                    // تحديث عدد المنتجات المخفضة
                    // $('#sale').siblings('label').find('.count').text('(' + response.data.sale_count + ')');
                }
            }
        });
    }

    // عند تحميل الصفحة: ماتش الـ current_cat أوّلًا
    $(function() {
        const slug = filter_params.current_cat;
        if (slug) {
            $('.filter-cat').removeClass('active');
            $(`.filter-cat[data-cat="${slug}"]`).addClass('active');
        } else {
            // لو مش في أرشيف، اختار أول كاتيجوري
            $('.filter-cat').first().addClass('active');
        }
        // هات قيمة التاج من أي مفتاح محتمل
        const params = new URLSearchParams(window.location.search);
        const tagParam =
        params.get("product-tag") || params.get("product_tag") || params.get("tag");

        if (!tagParam) {
            applyFilter();
        }
    });
});

jQuery(document).ready(function($) {
    $('.color-filter-item').click(function(e) {
        e.preventDefault();

        const $this = $(this);
        const colorName = $this.data('color-name');
        const productIds = $this.data('product-ids');

        // إضافة كلاس نشط للعنصر المحدد
        $('.color-filter-item').removeClass('active');
        $this.addClass('active');

        // إظهار مؤشر تحميل
        $('.wrapper-shop').html('<div class="loading-spinner"></div>');

        // إرسال طلب AJAX
        $.ajax({
            type: 'POST',
            url: filter_params.ajax_url,
            data: {
                action: 'filter_products_by_color',
                color_name: colorName,
                product_ids: productIds
            },
            success: function(response) {
                if (response.success) {
                    $('.wrapper-shop').html(response.data.html);

                    // تحديث عدد المنتجات
                    $('.product-count').text(response.data.count + ' products found');
                }
            },
            error: function(error) {
                console.error('AJAX Error:', error);
                $('.wrapper-shop').html('<p>Error loading products</p>');
            }
        });
    });
});


jQuery(function ($) {
  // هات قيمة التاج من أي مفتاح محتمل
  const params = new URLSearchParams(window.location.search);
  const tagParam =
  params.get("product-tag") || params.get("product_tag") || params.get("tag");

  if (tagParam) {
    // لو محتاج تتأكد إنه 65 تحديدًا:
    if (String(tagParam) === "65") {
       $.ajax({
         url: filter_params.ajax_url,
         type: "POST",
         dataType: "html",
         data: {
           action: "filter_products",
           tags: tagParam,
           nonce: filter_params.nonce,
         },
         beforeSend() {
           $(".wrapper-shop").css("opacity", 0.5);
         },
         success(html) {
           $(".wrapper-shop").html(html);
         },
         complete() {
           $(".wrapper-shop").css("opacity", 1);
         },
       });
    }

    // دا مفيد لو عندك فلترة AJAX وتعوز تمرر التاج
    window.currentTagParam = tagParam;

    // لو عندك applyFilter() وعايز تعيد التحميل مع التاج:
    if (typeof applyFilter === "function") {
      applyFilter();
    }
  }
});



jQuery(function ($) {
  var $form = $('form.variations_form');

  // دالة بتجمع الـ attributes الحالية من السيلكتات (في حالة احتجنا نشتق variation_id في السيرفر)
  function collectAttributes() {
    var attrs = {};
    $form.find('select[name^="attribute_"]').each(function () {
      var name = $(this).attr('name');       // مثال: attribute_pa_wazn
      var val  = $(this).val() || '';
      attrs[name] = val;
    });
    return attrs;
  }

  function requestPrice() {
    var variationId = parseInt($form.find('input.variation_id').val(), 10) || 0;
    var productId   = parseInt($form.data('product_id'), 10) || 0;

    // لو مفيش تطابق ولسه variation_id = 0، نبعت الـ attributes للسيرفر يحاول يلاقي الفارييشن
    var payload = {
      action: 'get_variation_price',
      variation_id: variationId,
      product_id: productId
      // , nonce: window.VarPrice?.nonce
    };

    if (!variationId) {
      payload.attributes = collectAttributes();
    }

    $.post(
      (window.ajaxurl || (window.wc_add_to_cart_params && wc_add_to_cart_params.ajax_url)),
      payload
    ).done(function (res) {
      if (res && res.success) {
        $('.js-variation-price').html(res.data.price_html);
      } else {
        $('.js-variation-price').empty();
      }
    }).fail(function () {
      $('.js-variation-price').empty();
    });
  }

  // كل ما يتغير اختيار أي سيلكت من الفارييشنز
  $form.on('change', 'select[name^="attribute_"]', function () {
    // ووكومرس هيحاول يحدّد الفارييشن ويحدّث input.hidden (variation_id)
    // نستنى لحظة صغيرة عشان ووكومرس يكتب القيمة، ثم نطلق AJAX
    setTimeout(requestPrice, 0);
  });

  // بمجرد ما ووكومرس يلاقي فارييشن مطابق (أفضل نقطة لطلب السعر)
  $form.on('found_variation', function () {
    requestPrice();
  });

  // تنظيف عند إلغاء الاختيارات
  $form.on('reset_data hide_variation', function () {
    $('.js-variation-price').empty();
  });

  // لو فيه اختيار مبدئي، اطلب السعر عند تحميل الصفحة
  $form.trigger('check_variations');
});

