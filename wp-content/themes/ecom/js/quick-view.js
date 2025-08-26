// ملف quick-view.js - ضعه في مجلد js في الثيم
(function($){
  $(document).ready(function(){

    // عند محاولة فتح المودال
    $('#quickView').on('show.bs.modal', function(event){
      var button    = $(event.relatedTarget);      // العنصر اللي ضغط عليه
      var productId = button.data('product-id');   // رقم المنتج
      var modal     = $(this);

      // عرض اللودينج وإخفاء المحتوى
      modal.find('.modal-loading').show();
      modal.find('.modal-body-content').hide();

      // مسح المحتوى القديم
      modal.find('#product-images').empty();
      modal.find('#product-name').text('').attr('href','#');
      modal.find('#product-rating').empty();
      modal.find('#stock-info').empty();
      modal.find('#product-price').empty();
      modal.find('#product-description').empty();
      modal.find('#product-variants').empty();
      modal.find('#availability-info').empty();
      modal.find('#view-details-link').attr('href','#');
      modal.find('#add-to-cart-btn').data('variation-id','');

      // AJAX لجلب بيانات المنتج
      $.ajax({
        url: quickview_ajax.ajax_url,
        method: 'POST',
        dataType: 'json',
        data: {
          action:     'product_quick_view',
          product_id: productId,
          nonce:      quickview_ajax.nonce
        },
        success: function(response){
          if (response.success) {
            var d = response.data;

            // الصور
            var imgs = '';
            d.images.forEach(function(img){
              imgs += '<div class="swiper-slide"><img src="'+img.src+'" alt="'+img.alt+'"></div>';
            });
            modal.find('#product-images').html(imgs);
            // هنا ممكن تحتاج تنادي init أو update للـ Swiper

            // الاسم والرابط
            modal.find('#product-name')
                 .text(d.name)
                 .attr('href', d.permalink);

            // التقييم
            var stars = '';
            for (var i=1;i<=5;i++){
              stars += i <= Math.round(d.rating) 
                ? '<i class="icon icon-star-fill"></i>' 
                : '<i class="icon icon-star"></i>';
            }
            modal.find('#product-rating').html(stars + ' <span>(' + d.review_count + ')</span>');

            // المخزون
            modal.find('#stock-info')
                 .text(d.stock_quantity + ' in stock');

            // السعر
            if (d.sale_price){
              modal.find('#product-price').html(
                '<del>'+ d.regular_price +'</del> <ins>'+ d.sale_price +'</ins> '+
                '<span class="discount">-'+ d.discount_percentage +'%</span>'
              );
            } else {
              modal.find('#product-price').text(d.price);
            }

            // الوصف
            modal.find('#product-description').text(d.description);

            // المتغيرات (لو موجودة)
            if (d.attributes && Object.keys(d.attributes).length){
              var vars = '<form id="quick-view-variants">';
              for (var attr in d.attributes){
                vars += '<label>'+ attr +'</label><select name="'+attr+'">';
                d.attributes[attr].forEach(function(term){
                  vars += '<option value="'+term+'">'+term+'</option>';
                });
                vars += '</select>';
              }
              vars += '</form>';
              modal.find('#product-variants').html(vars);
            }

            // زر الإضافة للسلة
            modal.find('#add-to-cart-btn')
                 .data('product-id', d.id);

            // رابط التفاصيل
            modal.find('#view-details-link')
                 .attr('href', d.permalink);

            // تفعيل الإظهار
            modal.find('.modal-loading').hide();
            modal.find('.modal-body-content').show();

          } else {
            modal.find('.modal-loading').hide();
            modal.find('.modal-body-content')
                 .html('<p>Failed to load product.</p>')
                 .show();
          }
        },
        error: function(){
          modal.find('.modal-loading').hide();
          modal.find('.modal-body-content')
               .html('<p>Error retrieving data.</p>')
               .show();
        }
      });
    });

    // أزرار زيادة/نقص الكمية
    $('.modal-quick-view').on('click', '.btn-increase', function(){
      var $in = $(this).siblings('input.quantity-product');
      $in.val( parseInt($in.val()) + 1 );
    });
    $('.modal-quick-view').on('click', '.btn-decrease', function(){
      var $in = $(this).siblings('input.quantity-product');
      var v = parseInt($in.val());
      if (v>1) $in.val(v - 1);
    });

    // إضافة للسلة داخل الـ Quick View
    $('.modal-quick-view').on('click', '.btn-add-to-cart', function(e){
      e.preventDefault();
      var $btn = $(this);
      var pid  = $btn.data('product-id');
      var vid  = $btn.data('variation-id') || 0;
      var qty  = $('.quantity-product').val();

      $.post(quickview_ajax.ajax_url, {
        action:       'add_to_cart_quick_view',
        product_id:   pid,
        variation_id: vid,
        quantity:     qty,
        nonce:        quickview_ajax.nonce
      }, function(res){
        if (res.success){
          // حدث إضافة للسلة
          $('.cart-count').text(res.data.cart_count);
          // إمكانية عرض رسالة نجاح
        } else {
          alert('Failed to add to cart');
        }
      }, 'json');
    });

  });
})(jQuery);

// jQuery(document).ready(function($) {
//     let currentProduct = null;
//     let swiper = null;
    
//     // معالج النقر على زر Quick View
//     $(document).on('click', '.quick-view-btn', function(e) {
//         e.preventDefault();
        
//         const productId = $(this).data('product-id');
//         loadProductQuickView(productId);
//     });
    
//     // تحميل بيانات المنتج
//     function loadProductQuickView(productId) {
//         // إظهار اللودر
//         $('.modal-loading').show();
//         $('.modal-body-content').hide();
        
//         $.ajax({
//             url: quickview_ajax.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'product_quick_view',
//                 product_id: productId,
//                 nonce: quickview_ajax.nonce
//             },
//             success: function(response) {
//                 if (response.success) {
//                     currentProduct = response.data;
//                     populateModal(response.data);
//                     $('.modal-loading').hide();
//                     $('.modal-body-content').show();
//                 }
//             },
//             error: function() {
//                 alert('خطأ في تحميل بيانات المنتج');
//                 $('#quickView').modal('hide');
//             }
//         });
//     }
    
//     // ملء الـ modal بالبيانات
//     function populateModal(product) {
//         // اسم المنتج
//         $('#product-name').text(product.name).attr('href', product.permalink);
        
//         // الوصف
//         $('#product-description').html(product.description);
        
//         // رابط التفاصيل
//         $('#view-details-link').attr('href', product.permalink);
        
//         // الصور
//         populateImages(product.images);
        
//         // السعر
//         populatePrice(product);
        
//         // التقييم
//         populateRating(product);
        
//         // معلومات المخزون
//         populateStock(product);
        
//         // الخصائص والمتغيرات
//         populateVariants(product);
        
//         // تحديث زر الإضافة للسلة
//         $('#add-to-cart-btn').data('product-id', product.id);
//     }
    
//     // ملء الصور
//     function populateImages(images) {
//         const wrapper = $('#product-images');
//         wrapper.empty();
        
//         images.forEach(function(image) {
//             const slide = $(`
//                 <div class="swiper-slide">
//                     <div class="item">
//                         <img class="lazyload" src="${image.src}" alt="${image.alt}">
//                     </div>
//                 </div>
//             `);
//             wrapper.append(slide);
//         });
        
//         // تهيئة Swiper
//         if (swiper) {
//             swiper.destroy(true, true);
//         }
        
//         swiper = new Swiper('.tf-single-slide', {
//             slidesPerView: 1,
//             spaceBetween: 10,
//             loop: true,
//             navigation: {
//                 nextEl: '.swiper-button-next',
//                 prevEl: '.swiper-button-prev',
//             },
//             pagination: {
//                 el: '.swiper-pagination',
//                 clickable: true,
//             },
//         });
//     }
    
//     // ملء السعر
//     function populatePrice(product) {
//         const priceWrap = $('#product-price');
//         priceWrap.empty();
        
//         if (product.sale_price) {
//             const discountHtml = product.discount_percentage ? 
//                 `<p class="badges-on-sale h6 fw-semibold">
//                     <span class="number-sale">-${product.discount_percentage}%</span>
//                 </p>` : '';
            
//             priceWrap.html(`
//                 <span class="price-new price-on-sale h2">$${product.sale_price}</span>
//                 <span class="price-old compare-at-price h6">$${product.regular_price}</span>
//                 ${discountHtml}
//             `);
//         } else {
//             priceWrap.html(`<span class="price-new h2">$${product.price}</span>`);
//         }
//     }
    
//     // ملء التقييم
//     function populateRating(product) {
//         const ratingDiv = $('#product-rating');
//         ratingDiv.empty();
        
//         if (product.rating > 0) {
//             const stars = generateStars(product.rating);
//             ratingDiv.html(`
//                 <div class="d-flex gap-4">
//                     ${stars}
//                 </div>
//                 <div class="reviews text-main">(${product.review_count} review)</div>
//             `);
//         }
//     }
    
//     // إنشاء النجوم
//     function generateStars(rating) {
//         let stars = '';
//         for (let i = 1; i <= 5; i++) {
//             const fillColor = i <= rating ? '#EF9122' : '#E5E5E5';
//             stars += `
//                 <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
//                     <path d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z" fill="${fillColor}"/>
//                 </svg>
//             `;
//         }
//         return stars;
//     }
    
//     // ملء معلومات المخزون
//     function populateStock(product) {
//         const stockInfo = $('#stock-info');
//         const availabilityInfo = $('#availability-info');
        
//         if (product.stock_quantity > 0) {
//             stockInfo.html(`
//                 <i class="icon icon-shopping-cart-simple"></i>
//                 <span class="h6">${product.stock_quantity} products available</span>
//             `);
//             availabilityInfo.text(`${product.stock_quantity} products available`);
//         } else {
//             stockInfo.html('<span class="h6 text-danger">Out of Stock</span>');
//             availabilityInfo.text('Out of Stock');
//         }
//     }
    
//     // ملء الخصائص والمتغيرات
//     function populateVariants(product) {
//         const variantsDiv = $('#product-variants');
//         variantsDiv.empty();
        
//         if (product.attributes && Object.keys(product.attributes).length > 0) {
//             Object.keys(product.attributes).forEach(function(attributeName) {
//                 const terms = product.attributes[attributeName];
//                 const attributeHtml = createAttributeSelector(attributeName, terms);
//                 variantsDiv.append(attributeHtml);
//             });
//         }
//     }
    
//     // إنشاء منتقي الخصائص
//     function createAttributeSelector(attributeName, terms) {
//         const cleanName = attributeName.replace('pa_', '').replace('attribute_', '');
//         const displayName = cleanName.charAt(0).toUpperCase() + cleanName.slice(1);
        
//         if (cleanName === 'color' || cleanName === 'colour') {
//             return createColorSelector(displayName, terms);
//         } else if (cleanName === 'size') {
//             return createSizeSelector(displayName, terms);
//         } else {
//             return createGenericSelector(displayName, terms);
//         }
//     }
    
//     // منتقي الألوان
//     function createColorSelector(displayName, colors) {
//         let colorOptions = '';
//         colors.forEach(function(color, index) {
//             const isActive = index === 0 ? 'active' : '';
//             colorOptions += `
//                 <div class="hover-tooltip tooltip-bot color-btn ${isActive}" data-color="${color}">
//                     <span class="check-color bg-${color}"></span>
//                     <span class="tooltip">${color.charAt(0).toUpperCase() + color.slice(1)}</span>
//                 </div>
//             `;
//         });
        
//         return `
//             <div class="variant-picker-item variant-color">
//                 <div class="variant-picker-label">
//                     <div class="h4 fw-semibold">
//                         ${displayName}
//                         <span class="variant-picker-label-value value-currentColor">${colors[0]}</span>
//                     </div>
//                 </div>
//                 <div class="variant-picker-values">
//                     ${colorOptions}
//                 </div>
//             </div>
//         `;
//     }
    
//     // منتقي الأحجام
//     function createSizeSelector(displayName, sizes) {
//         let sizeOptions = '';
//         sizes.forEach(function(size, index) {
//             const isActive = index === 0 ? 'active' : '';
//             sizeOptions += `<span class="size-btn ${isActive}" data-size="${size}">${size.toUpperCase()}</span>`;
//         });
        
//         return `
//             <div class="variant-picker-item variant-size">
//                 <div class="variant-picker-label">
//                     <div class="h4 fw-semibold">
//                         ${displayName}
//                         <span class="variant-picker-label-value value-currentSize">${sizes[0]}</span>
//                     </div>
//                 </div>
//                 <div class="variant-picker-values">
//                     ${sizeOptions}
//                 </div>
//             </div>
//         `;
//     }
    
//     // منتقي عام
//     function createGenericSelector(displayName, options) {
//         let optionElements = '';
//         options.forEach(function(option, index) {
//             const isActive = index === 0 ? 'active' : '';
//             optionElements += `<span class="option-btn ${isActive}" data-option="${option}">${option}</span>`;
//         });
        
//         return `
//             <div class="variant-picker-item">
//                 <div class="variant-picker-label">
//                     <div class="h4 fw-semibold">
//                         ${displayName}
//                         <span class="variant-picker-label-value">${options[0]}</span>
//                     </div>
//                 </div>
//                 <div class="variant-picker-values">
//                     ${optionElements}
//                 </div>
//             </div>
//         `;
//     }
    
//     // معالج اختيار الألوان
//     $(document).on('click', '.color-btn', function() {
//         $('.color-btn').removeClass('active');
//         $(this).addClass('active');
        
//         const selectedColor = $(this).data('color');
//         $('.value-currentColor').text(selectedColor);
        
//         // تحديث الصورة حسب اللون إذا كان متاحاً
//         updateImageByColor(selectedColor);
//     });
    
//     // معالج اختيار الأحجام
//     $(document).on('click', '.size-btn', function() {
//         $('.size-btn').removeClass('active');
//         $(this).addClass('active');
        
//         const selectedSize = $(this).data('size');
//         $('.value-currentSize').text(selectedSize);
//     });
    
//     // معالج اختيار الخيارات العامة
//     $(document).on('click', '.option-btn', function() {
//         $(this).siblings().removeClass('active');
//         $(this).addClass('active');
        
//         const selectedOption = $(this).data('option');
//         $(this).closest('.variant-picker-item').find('.variant-picker-label-value').text(selectedOption);
//     });
    
//     // تحديث الصورة حسب اللون
//     function updateImageByColor(color) {
//         if (currentProduct && currentProduct.variations) {
//             currentProduct.variations.forEach(function(variation) {
//                 if (variation.attributes && variation.attributes['attribute_pa_color'] === color) {
//                     // تحديث الصورة الرئيسية
//                     if (variation.image && variation.image.src) {
//                         $('.swiper-slide').first().find('img').attr('src', variation.image.src);
//                     }
//                 }
//             });
//         }
//     }
    
//     // معالج تغيير الكمية
//     $(document).on('click', '.btn-decrease', function() {
//         const input = $(this).siblings('.quantity-product');
//         const currentValue = parseInt(input.val());
//         if (currentValue > 1) {
//             input.val(currentValue - 1);
//         }
//     });
    
//     $(document).on('click', '.btn-increase', function() {
//         const input = $(this).siblings('.quantity-product');
//         const currentValue = parseInt(input.val());
//         input.val(currentValue + 1);
//     });
    
//     // معالج إضافة المنتج للسلة
//     $(document).on('click', '#add-to-cart-btn', function(e) {
//         e.preventDefault();
        
//         const productId = $(this).data('product-id');
//         const quantity = $('.quantity-product').val();
        
//         // جمع الخصائص المختارة
//         const selectedAttributes = {};
//         $('.variant-picker-item').each(function() {
//             const attributeName = $(this).find('.variant-picker-label-value').text();
//             if (attributeName) {
//                 selectedAttributes[attributeName] = attributeName;
//             }
//         });
        
//         // العثور على المتغير المناسب
//         let variationId = 0;
//         if (currentProduct.variations) {
//             currentProduct.variations.forEach(function(variation) {
//                 // منطق مطابقة الخصائص
//                 variationId = variation.variation_id;
//             });
//         }
        
//         // إضافة للسلة
//         addToCart(productId, quantity, variationId);
//     });
    
//     // إضافة المنتج للسلة
//     function addToCart(productId, quantity, variationId = 0) {
//         $.ajax({
//             url: quickview_ajax.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'add_to_cart_quick_view',
//                 product_id: productId,
//                 quantity: quantity,
//                 variation_id: variationId,
//                 nonce: quickview_ajax.nonce
//             },
//             success: function(response) {
//                 if (response.success) {
//                     // إظهار رسالة نجاح
//                     showSuccessMessage('تم إضافة المنتج للسلة بنجاح');
                    
//                     // تحديث عداد السلة
//                     updateCartCount(response.data.cart_count);
                    
//                     // إغلاق الـ modal
//                     $('#quickView').modal('hide');
//                 } else {
//                     showErrorMessage('فشل في إضافة المنتج للسلة');
//                 }
//             },
//             error: function() {
//                 showErrorMessage('خطأ في الاتصال بالخادم');
//             }
//         });
//     }
    
//     // إظهار رسالة نجاح
//     function showSuccessMessage(message) {
//         // يمكنك استخدام أي مكتبة للإشعارات أو إنشاء إشعار مخصص
//         alert(message);
//     }
    
//     // إظهار رسالة خطأ
//     function showErrorMessage(message) {
//         alert(message);
//     }
    
//     // تحديث عداد السلة
//     function updateCartCount(count) {
//         $('.cart-count').text(count);
//     }
    
//     // تنظيف الـ modal عند إغلاقه
//     $('#quickView').on('hidden.bs.modal', function() {
//         currentProduct = null;
//         if (swiper) {
//             swiper.destroy(true, true);
//             swiper = null;
//         }
//     });
// });