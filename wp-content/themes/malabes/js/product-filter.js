jQuery(document).ready(function ($) {

    // متغيرات عامة
    let currentPage = 1;
    let isLoading = false;

    // تهيئة الفلاتر
    initializeFilters();

    // تحميل المنتجات عند تحميل الصفحة
    loadProducts();

    // تهيئة الفلاتر
    function initializeFilters() {

        // تحديث شريط السعر
        updatePriceSlider();

        // مراقبة تغيير الفلاتر
        watchFilterChanges();

        // مراقبة تغيير الترتيب
        watchSortingChanges();

        // مراقبة تغيير عدد المنتجات في الصفحة
        watchPerPageChanges();

        // زر مسح جميع الفلاتر
        $('#reset-filter, #remove-all').on('click', function (e) {
            e.preventDefault();
            resetAllFilters();
        });

        // مراقبة clicks على pagination
        $(document).on('click', '.pagination-item', function (e) {
            e.preventDefault();
            let page = $(this).data('page');
            if (page && !isLoading) {
                currentPage = page;
                loadProducts();
            }
        });
    }

    // تحديث شريط السعر
    function updatePriceSlider() {

        const minPriceSlider = document.getElementById('min-price');
        const maxPriceSlider = document.getElementById('max-price');
        const minPriceDisplay = document.getElementById('min-price-display');
        const maxPriceDisplay = document.getElementById('max-price-display');

        if (minPriceSlider && maxPriceSlider) {
            function updateSlider() {
                let minVal = parseInt(minPriceSlider.value);
                let maxVal = parseInt(maxPriceSlider.value);

                // تأكد من أن الحد الأدنى أقل من الحد الأقصى
                if (minVal > maxVal) {
                    minVal = maxVal;
                    minPriceSlider.value = minVal;
                }

                minPriceDisplay.textContent = minVal;
                maxPriceDisplay.textContent = maxVal;

                // تحديث المنتجات
                loadProducts();
            }

            minPriceSlider.addEventListener('input', updateSlider);
            maxPriceSlider.addEventListener('input', updateSlider);
        }
    }

    // مراقبة تغيير الفلاتر
    function watchFilterChanges() {
        // مراقبة checkboxes
        $('.filter-checkbox').on('change', function () {
            currentPage = 1;
            loadProducts();
            updateActiveFilters();
        });

        // مراقبة radio buttons
        $('.filter-radio').on('change', function () {
            currentPage = 1;
            loadProducts();
            updateActiveFilters();
        });

    }

    // مراقبة تغيير الترتيب
    function watchSortingChanges() {
        $('#orderby-select').on('change', function () {
            currentPage = 1;
            loadProducts();
        });
    }

    // مراقبة تغيير عدد المنتجات في الصفحة
    function watchPerPageChanges() {
        $('#perpage-select').on('change', function () {
            currentPage = 1;
            loadProducts();
        });
    }

    // تحميل المنتجات
    function loadProducts() {
        if (isLoading) return;

        isLoading = true;
        showLoading();

        // جمع بيانات الفلاتر
        const filterData = collectFilterData();

        // Ajax request
        $.ajax({
            url: ajax_filter.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                nonce: ajax_filter.nonce,
                categories: filterData.categories,
                brands: filterData.brands,
                min_price: filterData.min_price,
                max_price: filterData.max_price,
                availability: filterData.availability,
                orderby: filterData.orderby,
                posts_per_page: filterData.posts_per_page,
                paged: currentPage
            },

            beforeSend() {
                console.log('🔸 sending →', this.data);      // شوف فعلاً البوست بيتبعت
            },
            success: function (response) {
                if (response.success) {
                    updateProductsDisplay(response.data);
                    updateProductCount(response.data.found_posts);
                    updateURL(filterData);
                    console.log('🔸 sending →', this.data); 

                } else {
                    showError('حدث خطأ أثناء تحميل المنتجات');
                }
            },
            error: function () {
                showError('فشل في الاتصال بالخادم');
            },
            complete: function () {
                hideLoading();
                isLoading = false;
            }
        });
    }

    // جمع بيانات الفلاتر
    function collectFilterData() {
        const categories = [];
        const brands = [];

        // جمع التصنيفات المحددة
        $('input[name="categories[]"]:checked').each(function () {
            categories.push($(this).val());
        });

        // جمع البراندات المحددة
        $('input[name="brands[]"]:checked').each(function () {
            brands.push($(this).val());
        });

        return {
            categories: categories,
            brands: brands,
            min_price: $('#min-price').val() || 0,
            max_price: $('#max-price').val() || 9999999,
            availability: $('input[name="availability"]:checked').val() || '',
            orderby: $('#orderby-select').val() || 'menu_order',
            posts_per_page: $('#perpage-select').val() || 12
        };
    }

    // تحديث عرض المنتجات
    function updateProductsDisplay(data) {
        $('#products-grid').html(data.products);

        // إضافة animation للمنتجات الجديدة
        $('#products-grid .card-product').addClass('fade-in');

        // التمرير لأعلى عند تغيير الصفحة
        if (currentPage > 1) {
            $('html, body').animate({
                scrollTop: $('#products-container').offset().top - 100
            }, 500);
        }
    }

    // تحديث عدد المنتجات
    function updateProductCount(count) {
        $('.product-count-number').text(count);

        if (count === 0) {
            $('#product-count-grid').html('<span class="text-muted">لا توجد منتجات</span>');
        } else {
            $('#product-count-grid').html('<span class="product-count-number">' + count + '</span> منتج');
        }
    }

    // تحديث الفلاتر النشطة
    function updateActiveFilters() {
        const activeFilters = [];
        const filterContainer = $('.active-filters');

        // فحص التصنيفات
        $('input[name="categories[]"]:checked').each(function () {
            const label = $(this).siblings('label').find('span').first().text();
            activeFilters.push({
                type: 'category',
                value: $(this).val(),
                label: label,
                element: $(this)
            });
        });

        // فحص البراندات
        $('input[name="brands[]"]:checked').each(function () {
            const label = $(this).siblings('label').find('span').first().text();
            activeFilters.push({
                type: 'brand',
                value: $(this).val(),
                label: label,
                element: $(this)
            });
        });

        // فحص التوفر
        const availability = $('input[name="availability"]:checked').val();
        if (availability && availability !== '') {
            const label = $('input[name="availability"]:checked').siblings('label').find('span').text();
            activeFilters.push({
                type: 'availability',
                value: availability,
                label: label,
                element: $('input[name="availability"]:checked')
            });
        }

        // فحص السعر
        const minPrice = parseInt($('#min-price').val());
        const maxPrice = parseInt($('#max-price').val());
        const originalMin = parseInt($('#min-price').attr('min'));
        const originalMax = parseInt($('#max-price').attr('max'));

        if (minPrice > originalMin || maxPrice < originalMax) {
            activeFilters.push({
                type: 'price',
                value: minPrice + '-' + maxPrice,
                label: 'السعر: ' + minPrice + ' - ' + maxPrice,
                element: null
            });
        }

        // عرض الفلاتر النشطة
        if (activeFilters.length > 0) {
            let filtersHTML = '';
            activeFilters.forEach(function (filter) {
                filtersHTML += '<span class="active-filter-item" data-type="' + filter.type + '" data-value="' + filter.value + '">';
                filtersHTML += filter.label;
                filtersHTML += '<i class="icon icon-close remove-filter"></i>';
                filtersHTML += '</span>';
            });

            filterContainer.html(filtersHTML);
            $('#remove-all').show();
        } else {
            filterContainer.empty();
            $('#remove-all').hide();
        }
    }

    // إزالة فلتر واحد
    $(document).on('click', '.remove-filter', function () {
        const filterItem = $(this).parent();
        const type = filterItem.data('type');
        const value = filterItem.data('value');

        // إزالة الفلتر حسب النوع
        if (type === 'category') {
            $('input[name="categories[]"][value="' + value + '"]').prop('checked', false);
        } else if (type === 'brand') {
            $('input[name="brands[]"][value="' + value + '"]').prop('checked', false);
        } else if (type === 'availability') {
            $('input[name="availability"][value="' + value + '"]').prop('checked', false);
            $('input[name="availability"][value=""]').prop('checked', true);
        } else if (type === 'price') {
            $('#min-price').val($('#min-price').attr('min'));
            $('#max-price').val($('#max-price').attr('max'));
            $('#min-price-display').text($('#min-price').attr('min'));
            $('#max-price-display').text($('#max-price').attr('max'));
        }

        currentPage = 1;
        loadProducts();
        updateActiveFilters();
    });

    // مسح جميع الفلاتر
    function resetAllFilters() {
        // مسح checkboxes
        $('.filter-checkbox').prop('checked', false);

        // مسح radio buttons والعودة للافتراضي
        $('.filter-radio').prop('checked', false);
        $('#allStock').prop('checked', true);

        // إعادة تعيين السعر
        $('#min-price').val($('#min-price').attr('min'));
        $('#max-price').val($('#max-price').attr('max'));
        $('#min-price-display').text($('#min-price').attr('min'));
        $('#max-price-display').text($('#max-price').attr('max'));

        // إعادة تعيين الترتيب
        $('#orderby-select').val('menu_order');
        $('#perpage-select').val('12');

        currentPage = 1;
        loadProducts();
        updateActiveFilters();
    }

    // تحديث URL
    function updateURL(filterData) {
        const url = new URL(window.location);
        const params = new URLSearchParams();

        // إضافة المعاملات إلى URL
        if (filterData.categories.length > 0) {
            params.set('categories', filterData.categories.join(','));
        }
        if (filterData.brands.length > 0) {
            params.set('brands', filterData.brands.join(','));
        }
        if (filterData.min_price > 0) {
            params.set('min_price', filterData.min_price);
        }
        if (filterData.max_price < 9999999) {
            params.set('max_price', filterData.max_price);
        }
        if (filterData.availability) {
            params.set('availability', filterData.availability);
        }
        if (filterData.orderby !== 'menu_order') {
            params.set('orderby', filterData.orderby);
        }
        if (filterData.posts_per_page !== '12') {
            params.set('per_page', filterData.posts_per_page);
        }
        if (currentPage > 1) {
            params.set('page', currentPage);
        }

        // تحديث URL بدون إعادة تحميل الصفحة
        const newURL = url.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState(null, null, newURL);
    }

    // عرض حالة التحميل
    function showLoading() {
        $('.loading-spinner').show();
        $('#products-grid').addClass('loading');
    }

    // إخفاء حالة التحميل
    function hideLoading() {
        $('.loading-spinner').hide();
        $('#products-grid').removeClass('loading');
    }

    // عرض رسالة خطأ
    function showError(message) {
        $('#products-grid').html('<div class="error-message"><h3>' + message + '</h3><button class="tf-btn retry-btn">حاول مرة أخرى</button></div>');

        // إعادة المحاولة
        $(document).on('click', '.retry-btn', function () {
            loadProducts();
        });
    }

    // إضافة للسلة
    $(document).on('click', '.add-to-cart-btn', function (e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const button = $(this);

        button.addClass('loading');

        $.ajax({
            url: ajax_filter.ajax_url,
            type: 'POST',
            data: {
                action: 'add_to_cart',
                product_id: productId,
                nonce: ajax_filter.nonce
            },
            success: function (response) {
                if (response.success) {
                    button.removeClass('loading').addClass('added');
                    button.find('.tooltip').text('تم الإضافة');

                    // تحديث عدد المنتجات في السلة
                    updateCartCount(response.data.cart_count);

                    // إظهار رسالة نجاح
                    showNotification('تم إضافة المنتج للسلة', 'success');
                } else {
                    showNotification('فشل في إضافة المنتج للسلة', 'error');
                }
            },
            error: function () {
                showNotification('حدث خطأ أثناء إضافة المنتج للسلة', 'error');
            },
            complete: function () {
                button.removeClass('loading');
            }
        });
    });

    // إضافة للمفضلة
    $(document).on('click', '.wishlist-btn', function (e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const button = $(this);

        button.addClass('loading');

        $.ajax({
            url: ajax_filter.ajax_url,
            type: 'POST',
            data: {
                action: 'add_to_wishlist',
                product_id: productId,
                nonce: ajax_filter.nonce
            },
            success: function (response) {
                if (response.success) {
                    button.removeClass('loading').toggleClass('added');
                    const tooltipText = button.hasClass('added') ? 'تم الإضافة للمفضلة' : 'إضافة للمفضلة';
                    button.find('.tooltip').text(tooltipText);

                    showNotification(response.data.message, 'success');
                } else {
                    showNotification('فشل في إضافة المنتج للمفضلة', 'error');
                }
            },
            error: function () {
                showNotification('حدث خطأ أثناء إضافة المنتج للمفضلة', 'error');
            },
            complete: function () {
                button.removeClass('loading');
            }
        });
    });

    // عرض الإشعارات
    function showNotification(message, type) {
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);

        setTimeout(function () {
            notification.addClass('show');
        }, 100);

        setTimeout(function () {
            notification.removeClass('show');
            setTimeout(function () {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // تحديث عدد المنتجات في السلة
    function updateCartCount(count) {
        $('.cart-count').text(count);
    }

    // تحميل الفلاتر من URL عند تحميل الصفحة
    function loadFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);

        // تحميل التصنيفات
        const categories = urlParams.get('categories');
        if (categories) {
            categories.split(',').forEach(function (catId) {
                $('input[name="categories[]"][value="' + catId + '"]').prop('checked', true);
            });
        }

        // تحميل البراندات
        const brands = urlParams.get('brands');
        if (brands) {
            brands.split(',').forEach(function (brandId) {
                $('input[name="brands[]"][value="' + brandId + '"]').prop('checked', true);
            });
        }

        // تحميل السعر
        const minPrice = urlParams.get('min_price');
        const maxPrice = urlParams.get('max_price');
        if (minPrice) {
            $('#min-price').val(minPrice);
            $('#min-price-display').text(minPrice);
        }
        if (maxPrice) {
            $('#max-price').val(maxPrice);
            $('#max-price-display').text(maxPrice);
        }

        // تحميل التوفر
        const availability = urlParams.get('availability');
        if (availability) {
            $('input[name="availability"][value="' + availability + '"]').prop('checked', true);
        }

        // تحميل الترتيب
        const orderby = urlParams.get('orderby');
        if (orderby) {
            $('#orderby-select').val(orderby);
        }

        // تحميل عدد المنتجات في الصفحة
        const perPage = urlParams.get('per_page');
        if (perPage) {
            $('#perpage-select').val(perPage);
        }

        // تحميل رقم الصفحة
        const page = urlParams.get('page');
        if (page) {
            currentPage = parseInt(page);
        }

        updateActiveFilters();
    }

    // تحميل الفلاتر من URL
    loadFiltersFromURL();

});
