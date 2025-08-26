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



// jQuery(function ($) {
//   // عند الضغط على كاتيجوري
//   $(document).on("click", ".filter-cat", function (e) {
//     e.preventDefault();
//     $(".filter-cat, .filter-bar").removeClass("active");
//     $(this).addClass("active");
//     applyFilter();
//   });

//   // عند الضغط على براند
//   $(document).on("click", ".filter-bar", function (e) {
//     e.preventDefault();
//     $(".filter-cat, .filter-bar").removeClass("active");
//     $(this).addClass("active");
//     applyFilter();
//   });

//   // عند تغيير حالة checkbox الخاص بالتخفيضات
//   $(document).on("change", "#sale", function () {
//     applyFilter();
//   });

//   // عند تغيير حالة radio buttons الخاصة بالتوفر
//   $(document).on("change", 'input[name="availability"]', function () {
//     applyFilter();
//   });

//   function applyFilter() {
//     const categories = $(".filter-cat.active")
//       .map(function () {
//         return $(this).data("cat");
//       })
//       .get();
//     const brands = $(".filter-bar.active")
//       .map(function () {
//         return $(this).data("bar");
//       })
//       .get();

//     const saleOnly = $("#sale").is(":checked");
//     const availability =
//       $('input[name="availability"]:checked').attr("id") || "";

//     $.ajax({
//       url: filter_params.ajax_url,
//       type: "POST",
//       dataType: "html",
//       data: {
//         action: "filter_products",
//         categories: categories,
//         brands: brands,
//         sale_only: saleOnly,
//         availability: availability,
//         nonce: filter_params.nonce,
//       },
//       beforeSend() {
//         $(".wrapper-shop").css("opacity", 0.5);
//       },
//       success(html) {
//         $(".wrapper-shop").html(html);
//       },
//       complete() {
//         $(".wrapper-shop").css("opacity", 1);
//       },
//     });

//     updateFilterCounts();
//   }

//   function updateFilterCounts() {
//     $.ajax({
//       url: filter_params.ajax_url,
//       type: "POST",
//       dataType: "json",
//       data: {
//         action: "update_filter_counts",
//         nonce: filter_params.nonce,
//       },
//       success: function (response) {
//         if (response.success) {
//           $("#inStock")
//             .siblings("label")
//             .find(".count")
//             .text(response.data.instock_count);
//           $("#outStock")
//             .siblings("label")
//             .find(".count")
//             .text(response.data.outstock_count);
//         }
//       },
//     });
//   }

//   // دالة للحصول على slug التصنيف من الرابط الحالي
//   function getCurrentCategoryFromURL() {
//     const url = window.location.href;
//     const pathname = window.location.pathname;

//     // أنماط مختلفة للروابط
//     const patterns = [
//       /\/product-category\/([^\/\?]+)/, // العادي
//       /\/ar\/product-category\/([^\/\?]+)/, // العربي
//       /\/en\/product-category\/([^\/\?]+)/, // الإنجليزي
//       /category\/([^\/\?]+)/, // حالات أخرى
//     ];

//     for (let pattern of patterns) {
//       const match = pathname.match(pattern);
//       if (match && match[1]) {
//         return decodeURIComponent(match[1]);
//       }
//     }

//     return null;
//   }

//   // دالة للحصول على التصنيف من breadcrumbs أو العنوان
//   function getCategoryFromPage() {
//     // من breadcrumbs
//     let categoryName = $(".woocommerce-breadcrumb a:last").text().trim();

//     // من عنوان الصفحة
//     if (!categoryName) {
//       categoryName = $(".page-title, h1.entry-title, .archive-title")
//         .first()
//         .text()
//         .trim();
//     }

//     // من body class
//     if (!categoryName) {
//       const bodyClasses = $("body").attr("class") || "";
//       const categoryMatch = bodyClasses.match(/product-category-([^\s]+)/);
//       if (categoryMatch) {
//         categoryName = categoryMatch[1].replace(/-/g, " ");
//       }
//     }

//     return categoryName;
//   }

//   // دالة للحصول على ID التصنيف من slug أو اسم (عربي أو إنجليزي)
//   function getCategoryIdFromSlug(identifier) {
//     let categoryId = null;

//     $(".filter-cat").each(function () {
//       const catSlug = $(this).data("cat");
//       const catName = $(this).data("cat-name") || $(this).text().trim();
//       const catId = $(this).data("cat-id");

//       // مقارنات متعددة
//       if (
//         catSlug === identifier ||
//         catName === identifier ||
//         catName.toLowerCase() === identifier.toLowerCase() ||
//         decodeURIComponent(catSlug) === identifier ||
//         encodeURIComponent(catName) === identifier ||
//         catSlug === encodeURIComponent(identifier) ||
//         catSlug === identifier.replace(/\s+/g, "-")
//       ) {
//         categoryId = catSlug;
//         return false; // توقف عن اللوب
//       }
//     });

//     return categoryId;
//   }

//   // عند تحميل الصفحة
//   $(function () {
//     let currentCat = null;

//     // الطريقة 1: من filter_params
//     if (filter_params.current_cat) {
//       currentCat = filter_params.current_cat;
//       console.log("التصنيف من filter_params:", currentCat);
//     }

//     // الطريقة 2: من الـ URL
//     if (!currentCat) {
//       const urlCategory = getCurrentCategoryFromURL();
//       if (urlCategory) {
//         currentCat = getCategoryIdFromSlug(urlCategory);
//         console.log(
//           "التصنيف من URL:",
//           urlCategory,
//           "-> تم تحويله إلى:",
//           currentCat
//         );
//       }
//     }

//     // الطريقة 3: من محتوى الصفحة
//     if (!currentCat) {
//       const pageCategory = getCategoryFromPage();
//       if (pageCategory) {
//         currentCat = getCategoryIdFromSlug(pageCategory);
//         console.log(
//           "التصنيف من الصفحة:",
//           pageCategory,
//           "-> تم تحويله إلى:",
//           currentCat
//         );
//       }
//     }

//     // تطبيق التصنيف المحدد
//     if (currentCat) {
//       $(".filter-cat").removeClass("active");
//       $(".list-item").removeClass("active");

//       const $targetCat = $(`.filter-cat[data-cat="${currentCat}"]`);

//       if ($targetCat.length) {
//         $targetCat.addClass("active");
//         $targetCat.closest(".list-item").addClass("active");
//         console.log("تم تفعيل التصنيف:", currentCat);
//       } else {
//         // محاولة أخيرة: البحث بالاسم
//         $(".filter-cat").each(function () {
//           const catName = $(this).data("cat-name") || $(this).text().trim();
//           if (
//             catName === currentCat ||
//             catName.toLowerCase() === currentCat.toLowerCase()
//           ) {
//             $(this).addClass("active");
//             $(this).closest(".list-item").addClass("active");
//             console.log("تم تفعيل التصنيف بالاسم:", catName);
//             return false;
//           }
//         });
//       }
//     } else {
//       // لو مش في أرشيف، اختار أول كاتيجوري
//       $(".filter-cat").first().addClass("active");
//       $(".list-item").first().addClass("active");
//       console.log("تم تفعيل التصنيف الافتراضي");
//     }

//     // التحقق من وجود تاج في الـ URL
//     const params = new URLSearchParams(window.location.search);
//     const tagParam =
//       params.get("product-tag") ||
//       params.get("product_tag") ||
//       params.get("tag");

//     // تطبيق الفلتر إذا لم يكن هناك تاج
//     if (!tagParam) {
//       applyFilter();
//     }

//     console.log("التصنيف النهائي المحدد:", currentCat);
//     console.log("عدد التصنيفات النشطة:", $(".filter-cat.active").length);
//   });
// });


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
