jQuery(document).ready(function ($) {
  // عند تغيير أي select من الـ variations
  $(".dynamic-product-container").on(
    "change",
    ".variation-select",
    function () {
      var container = $(this).closest(".dynamic-product-container");
      var productId = container.data("product-id");
      var allSelects = container.find(".variation-select");
      var attributes = {};
      var allSelected = true;

      // جمع جميع الـ attributes المحددة
      allSelects.each(function () {
        var attributeName = $(this).data("attribute");
        var attributeValue = $(this).val();

        if (attributeValue === "") {
          allSelected = false;
        } else {
          attributes[attributeName] = attributeValue;
        }
      });

      // إذا تم تحديد جميع الـ attributes
      if (allSelected && Object.keys(attributes).length > 0) {
        updatePrice(container, productId, attributes);
      } else {
        // إعادة تعيين السعر إذا لم يتم تحديد كل شيء
        container.find(".current-price").text("السعر: اختر جميع المواصفات");
      }
    }
  );

  function updatePrice(container, productId, attributes) {
    // إظهار loading
    container.find(".current-price").text("السعر: جاري التحميل...");

    $.ajax({
      type: "POST",
      url: variation_ajax.ajax_url,
      data: {
        action: "get_variation_price",
        product_id: productId,
        attributes: attributes,
        nonce: variation_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          container
            .find(".current-price")
            .html("السعر: " + response.data.price);

          // يمكنك إضافة المزيد من المعلومات هنا
          console.log("Variation ID:", response.data.variation_id);
          console.log("Raw Price:", response.data.raw_price);
        } else {
          container.find(".current-price").text("السعر: غير متاح");
        }
      },
      error: function () {
        container.find(".current-price").text("السعر: خطأ في التحميل");
      },
    });
  }

  // طريقة بديلة: استخدام الـ variations data المتاحة في الصفحة مباشرة
  // (أسرع من AJAX ولكن أقل مرونة)
  function updatePriceFromData(container, attributes) {
    var variationsData = JSON.parse(container.find(".variation-data").text());
    var matchingVariation = null;

    // البحث عن الـ variation المطابق
    for (var i = 0; i < variationsData.length; i++) {
      var variation = variationsData[i];
      var match = true;

      for (var attrName in attributes) {
        var variationAttrKey =
          "attribute_" + attrName.toLowerCase().replace(/[^a-z0-9]/g, "");

        if (
          !variation.attributes[variationAttrKey] ||
          variation.attributes[variationAttrKey] !== attributes[attrName]
        ) {
          match = false;
          break;
        }
      }

      if (match) {
        matchingVariation = variation;
        break;
      }
    }

    if (matchingVariation) {
      container
        .find(".current-price")
        .html("السعر: " + matchingVariation.price_html);
    } else {
      container.find(".current-price").text("السعر: غير متاح");
    }
  }
});

// دالة إضافية لإعادة تعيين جميع الـ selects
function resetVariationSelects(containerId) {
  jQuery("#" + containerId + " .variation-select").each(function () {
    jQuery(this).val("").trigger("change");
  });
}

// دالة للحصول على الـ variation المحدد حالياً
function getCurrentVariation(containerId) {
  var container = jQuery("#" + containerId);
  var attributes = {};

  container.find(".variation-select").each(function () {
    var attrName = jQuery(this).data("attribute");
    var attrValue = jQuery(this).val();

    if (attrValue !== "") {
      attributes[attrName] = attrValue;
    }
  });

  return attributes;
}
