document.addEventListener("DOMContentLoaded", function () {
  // Avatar upload (زي ما هو)
  const fileInput = document.querySelector(".fileInputDash");
  const avatarForm = document.getElementById("avatar-form");
  if (fileInput && avatarForm) {
    fileInput.addEventListener("change", function () {
      if (this.files && this.files[0]) avatarForm.submit();
    });
  }

  // استخدم الرابط والـ nonce الجاهزين من PHP
  const ajaxUrl = myAccountAjax.ajaxUrl;
  const ajaxNonce = myAccountAjax.nonce;

  const ordersWrap = document.getElementById("orders-list-wrap");
  const detailWrap = document.getElementById("order-detail-wrap");

  // دالة صغيرة للمطابقة مع أقرب عنصر (عشان الأيقونات داخل الزر ما تكسرش التارجت)
//   function closest(el, selector) {
//     return el && el.closest ? el.closest(selector) : null;
//   }

  // View Order Details
  document.addEventListener("click", function (e) {
     const btn = e.target.closest(".js-view-order"); // بدل closest(...) المساعدة
     if (!btn) return;

     e.preventDefault();
     const orderId = btn.getAttribute("data-order-id");
    if (!orderId || !detailWrap) return;

    detailWrap.style.display = "block";
    console.log(myAccountAjax.lengo);
    const text = myAccountAjax.lengo == 'ar' ? 'تحميل تفاصيل الطلب...' : 'Loading order details...';
    detailWrap.innerHTML =
      '<div class="p-4 text-center">' + text + '</div>';
    if (ordersWrap) ordersWrap.style.display = "none";

    // console.log(orderId);

    // const formData = new FormData();
    // formData.append("action", "get_order_details");
    // formData.append("order_id", orderId);
    // formData.append("nonce", ajaxNonce);

    console.log(myAccountAjax.lengo);
    $.ajax({
      type: "POST",
      url: myAccountAjax.ajaxUrl,
      data: { action: "get_order_details", order_id: orderId },
      success: function (response) {
        if (response.success) {
          detailWrap.innerHTML = response.data.html;
        }
      },
      error: function (error) {
        console.error("AJAX Error:", error);
      },
    });

  });

  // Back to Orders
  document.addEventListener("click", function (e) {
    const back = e.target.closest(".js-back-to-orders");
    if (!back) return;
    e.preventDefault();

    if (detailWrap) {
      detailWrap.style.display = "none";
      detailWrap.innerHTML = "";
    }
    if (ordersWrap) {
      ordersWrap.style.display = "block";
    }

    if (history.pushState) {
      const url = new URL(window.location.href);
      url.searchParams.delete("order_id");
      history.pushState({}, "", url.toString());
    }
  });

  // popstate
  window.addEventListener("popstate", function () {
    const url = new URL(window.location.href);
    const orderId = url.searchParams.get("order_id");
    if (!orderId) {
      if (detailWrap) {
        detailWrap.style.display = "none";
        detailWrap.innerHTML = "";
      }
      if (ordersWrap) ordersWrap.style.display = "block";
    } else {
      const btn = document.querySelector(
        '.js-view-order[data-order-id="' + orderId + '"]'
      );
      if (btn) btn.click();
    }
  });

  // لو فيه order_id في URL حمّله مباشرة
  (function () {
    const url = new URL(window.location.href);
    const orderId = url.searchParams.get("order_id");
    if (orderId) {
      const btn = document.querySelector(
        '.js-view-order[data-order-id="' + orderId + '"]'
      );
      if (btn) btn.click();
    }
  })();
});
