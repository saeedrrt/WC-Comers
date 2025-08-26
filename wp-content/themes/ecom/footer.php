<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ecom
 */
$lang = pll_current_language();
?>

<!-- Footer -->
<footer class="tf-footer">
  <div class="container d-flex">
    <span class="br-line"></span>
  </div>
  <div class="footer-body">
    <div class="container">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb_30 mb-xl-0">
          <div class="footer-col-block">
            <p class="footer-heading footer-heading-mobile"><?php echo $lang == 'ar' ? 'اتصل بنا' : 'Contact us'; ?></p>
            <div class="tf-collapse-content">
              <ul class="footer-contact">
                <li>
                  <i class="icon icon-map-pin"></i>
                  <span class="br-line"></span>
                  <a href="https://www.google.com/maps?q=8500+Lorem+Street+Chicago,+IL+55030+Dolor+sit+amet"
                    target="_blank" class="h6 link">
                    <?php echo $lang == 'ar' ? '8500 Lorem Street Chicago, IL 55030' : '8500 Lorem Street Chicago, IL 55030'; ?>
                    <br class="d-none d-lg-block"> <?php echo $lang == 'ar' ? 'Dolor sit amet' : 'Dolor sit amet'; ?>
                  </a>
                </li>
                <li>
                  <i class="icon icon-phone"></i>
                  <span class="br-line"></span>
                  <a href="tel:+88001234567" class="h6 link">+8(800) 123 4567</a>
                </li>
                <li>
                  <i class="icon icon-envelope-simple"></i>
                  <span class="br-line"></span>
                  <a href="mailto:themesflat@support.com" class="h6 link">themesflat@support.com</a>
                </li>
              </ul>
              <div class="social-wrap">
                <ul class="tf-social-icon">
                  <li>
                    <a href="https://www.facebook.com/" target="_blank" class="social-facebook">
                      <span class="icon"><i class="icon-fb"></i></span>
                    </a>
                  </li>
                  <li>
                    <a href="https://www.instagram.com/" target="_blank" class="social-instagram">
                      <span class="icon"><i class="icon-instagram-logo"></i></span>
                    </a>
                  </li>
                  <li>
                    <a href="https://x.com/" target="_blank" class="social-x">
                      <span class="icon"><i class="icon-x"></i></span>
                    </a>
                  </li>
                  <li>
                    <a href="https://www.tiktok.com/" target="_blank" class="social-tiktok">
                      <span class="icon"><i class="icon-tiktok"></i></span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-2 col-sm-6 mb_30 mb-xl-0">
          <div class="footer-col-block footer-wrap-1 ms-xl-auto">
            <p class="footer-heading footer-heading-mobile"><?php echo $lang == 'ar' ? 'التسوق' : 'Shopping'; ?></p>
            <div class="tf-collapse-content">
              <ul class="footer-menu-list">
                <li><a href="faq.html" class="link h6"><?php echo $lang == 'ar' ? 'شحن' : 'Shipping'; ?></a></li>
                <li><a href="shop-default.html" class="link h6"><?php echo $lang == 'ar' ? 'تسوق حسب العلامة التجارية' : 'Shop by Brand'; ?></a></li>
                <li><a href="track-order.html" class="link h6"><?php echo $lang == 'ar' ? 'تتبع الطلب' : 'Track order'; ?></a></li>
                <li><a href="faq.html" class="link h6">Terms & Conditions</a></li>
                <li><a href="#size-guide" data-bs-toggle="modal" class="link h6">Size Guide</a></li>
                <li><a href="wishlist.html" class="link h6">My Wishlist</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb_30 mb-sm-0">
          <div class="footer-col-block footer-wrap-2 mx-xl-auto">
            <p class="footer-heading footer-heading-mobile"><?php echo $lang == 'ar' ? 'معلومات' : 'Information'; ?></p>
            <div class="tf-collapse-content">
              <ul class="footer-menu-list">
                <li><a href="about-us.html" class="link h6"><?php echo $lang == 'ar' ? 'عننا' : 'About Us'; ?></a></li>
                <li><a href="faq.html" class="link h6"><?php echo $lang == 'ar' ? 'الشروط والسياسات' : 'Term & Policy'; ?></a></li>
                <li><a href="faq.html" class="link h6"><?php echo $lang == 'ar' ? 'مركز المساعدة' : 'Help Center'; ?></a></li>
                <li><a href="blog-grid.html" class="link h6">News & Blog</a></li>
                <li><a href="faq.html" class="link h6">Refunds</a></li>
                <li><a href="faq.html" class="link h6">Careers</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-xl-4 col-sm-6">
          <div class="footer-col-block">
            <p class="footer-heading footer-heading-mobile"><?php echo $lang == 'ar' ? 'اتصل بنا' : 'Let’s keep in touch'; ?></p>
            <div class="tf-collapse-content">
              <div class="footer-newsletter">
                <p class="h6 caption">
                  <?php echo $lang == 'ar' ? 'أدخل بريدك الإلكتروني أدناه لتصلك أولًا عن المجموعات الجديدة والمنتجات الجديدة.' : 'Enter your email below to be the first to know about new collections and product launches.'; ?>
                </p>
                <form class="form_sub has_check" id="subscribe-form">
                  <div class="f-content" id="subscribe-content">
                    <fieldset class="col">
                      <input class="style-stroke" id="subscribe-email" type="email" name="email-form"
                        placeholder="Enter your email" required>
                    </fieldset>
                    <button id="subscribe-button" type="button" class="tf-btn animate-btn type-small-2">
                      Subscribe
                      <i class="icon icon-arrow-right"></i>
                    </button>
                  </div>
                  <div class="checkbox-wrap">
                    <input id="remember" type="checkbox" class="tf-check style-3">
                    <label for="remember" class="h6">
                      <?php echo $lang == 'ar' ? 'By clicking subcribe, you agree to the' : 'By clicking subcribe, you agree to the'; ?>
                      <a href="faq.html" class="text-decoration-underline link"><?php echo $lang == 'ar' ? 'Terms of Service' : 'Terms of Service'; ?></a> and <a href="faq.html" class="text-decoration-underline link"><?php echo $lang == 'ar' ? 'Privacy Policy' : 'Privacy Policy'; ?></a>.
                    </label>
                  </div>
                  <div id="subscribe-msg"></div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <div class="inner-bottom">
        <ul class="list-hor">
          <li>
            <a href="#" class="h6 link">Help & FAQs</a>
          </li>
          <li class="br-line type-vertical"></li>
          <li>
            <a href="#" class="h6 link">Factory</a>
          </li>
        </ul>
        <div class="list-hor flex-wrap">
          <span class="h6">Payment:</span>
          <ul class="payment-method-list">
            <li><img src="<?= get_template_directory_uri() ?>/assets/images/payment/visa.png" alt="Payment"></li>
            <li><img src="<?= get_template_directory_uri() ?>/assets/images/payment/master-card.png" alt="Payment"></li>
            <li><img src="<?= get_template_directory_uri() ?>/assets/images/payment/amex.png" alt="Payment"></li>
            <li><img src="<?= get_template_directory_uri() ?>/assets/images/payment/discover.png" alt="Payment"></li>
            <li><img src="<?= get_template_directory_uri() ?>/assets/images/payment/paypal.png" alt="Payment"></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</footer>
<!-- /Footer -->
</div>

<!-- Mobile Menu -->
<div class="offcanvas offcanvas-start canvas-mb" id="mobileMenu">
  <span class="icon-close-popup" data-bs-dismiss="offcanvas">
    <i class="icon-close"></i>
  </span>
  <div class="canvas-header">
    <!-- <p class="text-logo-mb">Ochaka.</p> -->
    <ul class="topbar-right topbar-option-list">

         <?php
$languages = pll_the_languages(array('raw' => 1));
if (!empty($languages)): ?>

<li class="tf-languages d-none d-xl-block">
    <select class="tf-dropdown-select style-default color-white type-languages" id="languageSwitcher">
        <?php foreach ($languages as $lang): ?>
            <option value="<?= esc_url($lang['url']); ?>" 
                    <?= $lang['current_lang'] ? 'selected' : ''; ?>>
                <?= esc_html($lang['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</li>

<script>
document.getElementById('languageSwitcher').addEventListener('change', function() {
    if (this.value) window.location.href = this.value;
});
</script>

<?php endif; ?>
    
    </ul>
    <span class="br-line"></span>
  </div>
  <div class="canvas-body">
    <div class="mb-content-top">
      <!-- <ul class="nav-ul-mb" id="wrapper-menu-navigation"></ul> -->
       
                <ul class="nav-ul-mb">
                  <li class="menu-item">
                    <a href="javascript:void(0)" class="item-link"><?php echo $lango == 'ar' ? 'الرئيسية' : 'Home'; ?></a>
                  </li>
                  <li class="menu-item">
                    <a href="<?= home_url('/faq'); ?>" class="item-link"><?php echo $lango == 'ar' ? 'الأسئلة الشائعة' : 'FAQ'; ?></a>
                  </li>
                  <li class="menu-item">
                    <a href="<?= home_url('/contact-us'); ?>" class="item-link"><?php echo $lango == 'ar' ? 'اتصل بنا' : 'Contact'; ?></a>
                  </li>

                  <li>
                    <?php if (is_user_logged_in()): ?>
                    <a href="<?= site_url('/my-account'); ?>" class="tf-btn type-small style-2">
                      <?php echo $lango == 'ar' ? 'الحساب' : 'Account'; ?>
                      <i class="icon icon-user"></i>
                    </a>
                    <?php else: ?>
                    <a href="<?= site_url('/login'); ?>" class="tf-btn type-small style-2">
                      <?php echo $lango == 'ar' ? 'تسجيل الدخول' : 'Login'; ?>
                      <i class="icon icon-user"></i>
                    </a>
                    <?php endif; ?>
                  </li>

                  <style>
                    /* الكاتيجوري الرئيسي */
                    .main-cat-link {
                      font-weight: 700;
                      font-size: 15px;
                      display: inline-block;
                      color: #222;
                      text-transform: uppercase;
                      margin-bottom: 6px;
                    }

                    .main-cat-link:hover {
                      color: rgb(0, 28, 104);
                      /* لون عند الـ Hover */
                    }

                    /* قائمة السبكاتيجوري */
                    .styled-sub {
                      margin: 0 0 1rem;
                      padding-left: 0;
                      list-style: none;
                    }

                    .styled-sub li {
                      margin-bottom: 4px;
                    }

                    .sub-cat-link {
                      display: flex;
                      align-items: center;
                      font-size: 14px;
                      color: #555;
                      text-decoration: none;
                      transition: all 0.2s ease-in-out;
                    }

                    .sub-cat-link .sub-icon {
                      font-size: 12px;
                      margin-right: 6px;
                      color: #999;
                      transition: margin-right 0.2s ease-in-out, color 0.2s ease-in-out;
                    }

                    .sub-cat-link:hover {
                      color: rgb(0, 28, 104);
                    }

                    .sub-cat-link:hover .sub-icon {
                      margin-right: 10px;
                      color: rgb(0, 28, 104);
                    }
                  </style>


                  </li>

                </ul>
        
    </div>
    
    <div class="flow-us-wrap">
      <h5 class="title"><?php echo $lango == 'ar' ? 'تابعنا' : 'Follow us'; ?></h5>
      <ul class="tf-social-icon">
        <li>
          <a href="https://www.facebook.com/" target="_blank" class="social-facebook">
            <span class="icon"><i class="icon-fb"></i></span>
          </a>
        </li>
        <li>
          <a href="https://www.instagram.com/" target="_blank" class="social-instagram">
            <span class="icon"><i class="icon-instagram-logo"></i></span>
          </a>
        </li>
        <li>
          <a href="https://x.com/" target="_blank" class="social-x">
            <span class="icon"><i class="icon-x"></i></span>
          </a>
        </li>
        <li>
          <a href="https://www.tiktok.com/" target="_blank" class="social-tiktok">
            <span class="icon"><i class="icon-tiktok"></i></span>
          </a>
        </li>
      </ul>
    </div>

  </div>
 
</div>
<!-- /Mobile Menu -->


<!-- Size Guide -->
<div class="modal modalCentered fade modal-size-guide" id="size-guide">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content widget-tabs style-2">
      <div class="header">
        <ul class="widget-menu-tab">
          <li class="item-title active">
            <span class="inner h3">Size </span>
          </li>
          <li class="item-title">
            <span class="inner h3">Size Guide</span>
          </li>
        </ul>
        <span class="icon-close icon-close-popup" data-bs-dismiss="modal"></span>
      </div>
      <div class="wrap">
        <div class="widget-content-tab">
          <div class="widget-content-inner active">
            <div class="tab-size">
              <div>
                <div class="widget-size mb-24">
                  <div class="box-title-size">
                    <div class="title-size h6 text-black">Height</div>
                    <div class="number-size text-small">
                      <span class="max-size">100</span>
                      <span class="">Cm</span>
                    </div>
                  </div>
                  <div class="range-input">
                    <div class="tow-bar-block">
                      <div class="progress-size" style="width: 50%;"></div>
                    </div>
                    <input type="range" min="0" max="200" value="100" class="range-max">
                  </div>
                </div>
                <div class="widget-size">
                  <div class="box-title-size">
                    <div class="title-size h6 text-black">Weight</div>
                    <div class="number-size text-small">
                      <span class="max-size">50</span>
                      <span class="">Kg</span>
                    </div>
                  </div>
                  <div class="range-input">
                    <div class="tow-bar-block">
                      <div class="progress-size" style="width: 50%;"></div>
                    </div>
                    <input type="range" min="0" max="100" value="50" class="range-max">
                  </div>
                </div>
              </div>
              <div class="size-button-wrap choose-option-list">
                <div class="size-button-item choose-option-item">
                  <h6 class="text">Thin</h6>
                </div>
                <div class="size-button-item choose-option-item select-option">
                  <h6 class="text">Normal</h6>
                </div>
                <div class="size-button-item choose-option-item">
                  <h6 class="text">Plump</h6>
                </div>
              </div>
              <div class="suggests">
                <h4 class="">Suggests for you:</h4>
                <div class="suggests-list">
                  <a href="#" class="suggests-item link h6">L - shirt</a>
                  <a href="#" class="suggests-item link h6">XL - Pant</a>
                  <a href="#" class="suggests-item link h6">31 - Jeans</a>
                </div>
              </div>
            </div>
          </div>
          <div class="widget-content-inner overflow-auto text-nowrap">
            <table class="tab-sizeguide-table">
              <thead>
                <tr>
                  <th>Size</th>
                  <th>US</th>
                  <th>Bust</th>
                  <th>Waist</th>
                  <th>Low Hip</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>XS</td>
                  <td>2</td>
                  <td>32</td>
                  <td>24 - 25</td>
                  <td>33 - 34</td>
                </tr>
                <tr>
                  <td>S</td>
                  <td>4</td>
                  <td>26 - 27</td>
                  <td>34 - 35</td>
                  <td>35 - 26</td>
                </tr>
                <tr>
                  <td>M</td>
                  <td>6</td>
                  <td>28 - 29</td>
                  <td>36 - 37</td>
                  <td>38 - 40</td>
                </tr>
                <tr>
                  <td>L</td>
                  <td>8</td>
                  <td>30 - 31</td>
                  <td>38 - 29</td>
                  <td>42 - 44</td>
                </tr>
                <tr>
                  <td>XL</td>
                  <td>10</td>
                  <td>32 - 33</td>
                  <td>40 - 41</td>
                  <td>45 - 47</td>
                </tr>
                <tr>
                  <td>XXL</td>
                  <td>12</td>
                  <td>34 - 35</td>
                  <td>42 - 43</td>
                  <td>48 - 50</td>
                </tr>
              </tbody>
            </table>
          </div>


        </div>
      </div>
    </div>
  </div>
</div>
<!-- /Size Guide -->
  <!-- Compare -->
    <!-- Compare Off-canvas -->
<div class="offcanvas offcanvas-bottom canvas-compare" id="compare">
  <div class="canvas-wrapper">
    <div class="canvas-body">
      <div class="container">
        <div class="tf-compare-list wrap-empty_text">
          <div class="tf-compare-head"><h4 class="title"><?php echo $lango == 'ar' ? 'مقارنة المنتجات' : 'Compare products'; ?></h4></div>

          <div class="tf-compare-offcanvas">
            <?php
              $ids = compare_get_ids();
              if ( empty( $ids ) ) {
                echo '<p class="box-text_empty h6 text-main">Your Compare is currently empty</p>';
              } else {
                foreach ( $ids as $pid ) :
                  $p     = wc_get_product( $pid );
                  $img   = wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ); ?>
                  <div class="tf-compare-item" data-product-id="<?= esc_attr( $pid ); ?>">
                    <a href="<?= esc_url( get_permalink( $pid ) ); ?>">
                      <div class="icon remove compare-remove-btn"><i class="icon-close"></i></div>
                      <img class="radius-3" src="<?= esc_url( $img ); ?>" alt="<?= esc_attr( $p->get_name() ); ?>">
                    </a>
                  </div>
            <?php endforeach; } ?>
          </div>

          <div class="tf-compare-buttons" <?= empty( $ids ) ? 'style="display:none"' : ''; ?>>
            <a href="<?= site_url( '/compare' ); ?>" class="tf-btn bg-dark-2"><?php echo $lango == 'ar' ? 'مقارنة المنتجات' : 'Compare products'; ?></a>
            <button class="tf-btn btn-white line tf-compare-clear-all"><?php echo $lango == 'ar' ? 'مسح الكل' : 'Clear All'; ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- /Compare -->
     
<!-- Quick View -->
 
<!-- /Quick View -->
 
<!-- /Search -->
<!-- Shopping Cart -->
<?php woocommerce_mini_cart(); ?>
<!-- /Shopping Cart -->


<!-- Javascript -->
<script src="<?= get_template_directory_uri(); ?>/assets/js/jquery.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/bootstrap.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/swiper-bundle.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/carousel.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/bootstrap-select.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/lazysize.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/wow.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/parallaxie.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/count-down.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/photoswipe-lightbox.umd.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/photoswipe.umd.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/zoom.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/infinityslide.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/lazysize.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/nouislider.min.js"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/drift.min.js"></script>
  <!-- <script src="<?//= get_template_directory_uri(); ?>/assets/js/shop.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= get_template_directory_uri(); ?>/assets/js/main.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const langSelect = document.getElementById("languageSwitcher");
    langSelect.addEventListener("change", function () {
      const selectedUrl = this.value;
      if (selectedUrl) {
        window.location.href = selectedUrl;
      }
    });
  });
  
</script>

<?php wp_footer(); ?>
</body>

</html>