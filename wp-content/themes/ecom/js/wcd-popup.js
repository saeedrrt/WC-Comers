
jQuery(document).ready(function ($) {
  // عند النقر على أي عنصر له class "wcd-popup-trigger"
  $(document).on("click", ".wcd-popup-trigger", function (e) {
    e.preventDefault();

    var ruleId = $(this).data("rule-id");

    if (!ruleId) {
      console.log("Rule ID not found");
      return;
    }

    // إظهار البوب أب والتحميل
    $("#wcd-popup-overlay").show();
    $("#wcd-popup-loading").show();
    $("#wcd-popup-products").html("");

    // AJAX request لجلب المنتجات
    $.ajax({
      type: "POST",
      url: wcd_ajax.ajax_url,
      data: {
        action: "wcd_get_rule_products",
        rule_id: ruleId,
        // nonce: wcd_ajax.nonce,
      },
      success: function (response) {
        $("#wcd-popup-loading").hide();

        if (response.success) {
          // تحديث محتوى البوب أب
          $("#wcd-popup-title").text(response.data.rule_title);
          $("#wcd-popup-sale-text").text(response.data.sale_text);
          $("#wcd-popup-products").html(response.data.products_html);
        } else {
          $("#wcd-popup-products").html(
            "<p>Error loading products: " + response.data + "</p>"
          );
        }
      },
      error: function (xhr, status, error) {
        $("#wcd-popup-loading").hide();
        $("#wcd-popup-products").html(
          "<p>Error loading products. Please try again.</p>"
        );
        console.log("AJAX Error:", error);
      },
    });
  });

  // إغلاق البوب أب عند النقر على زر الإغلاق
  $(document).on("click", ".wcd-popup-close", function () {
    $("#wcd-popup-overlay").hide();
  });

  // إغلاق البوب أب عند النقر خارج المحتوى
  $(document).on("click", "#wcd-popup-overlay", function (e) {
    if (e.target === this) {
      $("#wcd-popup-overlay").hide();
    }
  });

  // إغلاق البوب أب بالضغط على Escape
  $(document).keyup(function (e) {
    if (e.keyCode === 27) {
      // ESC key
      $("#wcd-popup-overlay").hide();
    }
  });
});
