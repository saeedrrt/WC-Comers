// 4. الجافا سكريبت المطلوب - إضافة في ملف wc-ajax-search.js
jQuery(document).ready(function($) {
    let searchTimeout;
    let currentRequest;

    // البحث التلقائي أثناء الكتابة
    $('#wc-search-term').on('input', function() {
        clearTimeout(searchTimeout);
        
        if (currentRequest) {
            currentRequest.abort();
        }
        
        const term = $(this).val().trim();
        
        if (term.length < 2) {
            $('#wc-search-results').hide();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            performSearch(term);
        }, 300);
    });

    // إخفاء النتائج عند النقر خارج البحث
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-container').length) {
            $('#wc-search-results').hide();
        }
    });

    // تحديث الفئة المختارة
    $('#product_cat').on('change', function() {
        const term = $('#wc-search-term').val().trim();
        if (term.length >= 2) {
            performSearch(term);
        }
    });

    // البحث عند إرسال الفورم
    $('#wc-ajax-search-form').on('submit', function(e) {
        const term = $('#wc-search-term').val().trim();
        if (term.length < 2) {
            e.preventDefault();
            toastr.error('يرجى إدخال كلمة بحث مكونة من حرفين على الأقل');
        }
    });

    // النقر على عنصر النتيجة
    $(document).on('click', '.search-result-item', function(e) {
        if (!$(e.target).hasClass('view-product')) {
            const productUrl = $(this).find('.view-product').attr('href');
            window.location.href = productUrl;
        }
    });

    function performSearch(term) {
        const category = $('#product_cat').val();
        
        $('#wc-search-results').html('<div style="padding: 20px; text-align: center;">جاري البحث...</div>').show();
        
        currentRequest = $.ajax({
            url: ajax_search.url,
            type: 'POST',
            data: {
                action: 'wc_search_products',
                term: term,
                category: category
            },
            success: function(response) {
                $('#wc-search-results').html(response).show();
                
                // إضافة تأثير الظهور
                $('#wc-search-results').hide().fadeIn(300);
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    $('#wc-search-results').html('<div style="padding: 20px; text-align: center; color: red;">حدث خطأ أثناء البحث</div>').show();
                }
            }
        });
    }
});


