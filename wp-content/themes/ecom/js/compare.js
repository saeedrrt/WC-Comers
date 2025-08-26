jQuery(function ($) {
    /* ========== Helpers ========== */
    const ajax = compareVars.ajax;
    const nonce = compareVars.nonce;
  
    function updateCounter(cnt) { $('.compare-count').text(cnt); }
  
    function ensureButtons() {
      const wrap = $('.tf-compare-offcanvas');
      const btns = $('.tf-compare-buttons');
      if (wrap.find('.tf-compare-item').length) {
        btns.show();
        wrap.find('.box-text_empty').remove();
      } else {
        btns.hide();
        wrap.html('<p class="box-text_empty h6 text-main">Your Compare is currently empty</p>');
      }
    }
  
    /* ========== Add ========== */
    $(document).on('click', '.compare-btn', function () {
      const id = $(this).data('product-id');
      $.post(ajax, { action: 'add_to_compare', product_id: id, _ajax_nonce: nonce })
        .done(resp => {
          if (resp.success) {
            updateCounter(resp.data.count);
            // إرجاع HTML العنصر
            $.post(ajax, { action: 'get_compare_item', product_id: id, _ajax_nonce: nonce })
              .done(r => {
                if (r.success) {
                  $('.tf-compare-offcanvas').append(r.data);
                  ensureButtons();
                }
              });
          }
        });
      // افتح الـ off-canvas (Bootstrap 5)
      const bs = bootstrap.Offcanvas.getOrCreateInstance('#compare');
      bs.show();
    });
  
    /* ========== Remove single ========== */
    $(document).on('click', '.compare-remove-btn', function (e) {
      e.preventDefault();
      const $item = $(this).closest('.tf-compare-item');
      const id = $item.data('product-id');
      $.post(ajax, { action: 'remove_from_compare', product_id: id, _ajax_nonce: nonce })
        .done(resp => {
          if (resp.success) {
            $item.remove();
            updateCounter(resp.data.count);
            ensureButtons();
          }
        });
    });
  
    /* ========== Clear all ========== */
    $(document).on('click', '.tf-compare-clear-all', function () {
      $.post(ajax, { action: 'clear_compare', _ajax_nonce: nonce })
        .done(() => {
          $('.tf-compare-offcanvas').empty();
          updateCounter(0);
          ensureButtons();
        });
    });
  });
  