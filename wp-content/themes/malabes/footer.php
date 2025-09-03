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
<footer class="tf-footer style-color-white bg-black">
  <div class="footer-body">
    <div class="container">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb_30 mb-xl-0">
          <div class="footer-col-block">
            <p class="footer-heading footer-heading-mobile">Contact us</p>
            <div class="tf-collapse-content">
              <ul class="footer-contact">
                <li>
                  <i class="icon icon-map-pin"></i>
                  <span class="br-line"></span>
                  <a href="https://www.google.com/maps?q=8500+Lorem+Street+Chicago,+IL+55030+Dolor+sit+amet"
                    target="_blank" class="h6 link">
                    8500 Lorem Street Chicago, IL 55030 <br class="d-none d-lg-block"> Dolor sit amet
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
                <ul class="tf-social-icon style-2">
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
            <p class="footer-heading footer-heading-mobile">Shopping</p>
            <div class="tf-collapse-content">
              <ul class="footer-menu-list">
                <li><a href="faq.html" class="link h6">Shipping</a></li>
                <li><a href="shop-default.html" class="link h6">Shop by Brand</a></li>
                <li><a href="track-order.html" class="link h6">Track order</a></li>
                <li><a href="faq.html" class="link h6">Terms & Conditions</a></li>
                <li><a href="#size-guide" data-bs-toggle="modal" class="link h6">Size Guide</a></li>
                <li><a href="wishlist.html" class="link h6">My Wishlist</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb_30 mb-sm-0">
          <div class="footer-col-block footer-wrap-2 mx-xl-auto">
            <p class="footer-heading footer-heading-mobile">Information</p>
            <div class="tf-collapse-content">
              <ul class="footer-menu-list">
                <li><a href="about-us.html" class="link h6">About Us</a></li>
                <li><a href="faq.html" class="link h6">Term & Policy</a></li>
                <li><a href="faq.html" class="link h6">Help Center</a></li>
                <li><a href="blog-grid.html" class="link h6">News & Blog</a></li>
                <li><a href="faq.html" class="link h6">Refunds</a></li>
                <li><a href="faq.html" class="link h6">Careers</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-xl-4 col-sm-6">
          <div class="footer-col-block">
            <p class="footer-heading footer-heading-mobile">Let’s keep in touch</p>
            <div class="tf-collapse-content">
              <div class="footer-newsletter">
                <p class="h6 caption text-main-5">
                  Enter your email below to be the first to know about new collections and product launches.
                </p>
                <form class="form_sub has_check" id="subscribe-form">
                  <div class="f-content" id="subscribe-content">
                    <fieldset class="col">
                      <input class="style-stroke-2" id="subscribe-email" type="email" name="email-form"
                        placeholder="Enter your email" required>
                    </fieldset>
                    <button id="subscribe-button" type="button"
                      class="tf-btn btn-white animate-btn animate-dark type-small-2">
                      Subscribe
                      <i class="icon icon-arrow-right"></i>
                    </button>
                  </div>
                  <div class="checkbox-wrap">
                    <input id="remember" type="checkbox" class="tf-check style-3 style-white">
                    <label for="remember" class="h6 text-main-5">
                      By clicking subcribe, you agree to the  
                      <a href="faq.html" class="text-decoration-underline link text-main-5">Terms
                        of Service</a> and <a href="faq.html" class="text-decoration-underline link text-main-5">
                        Privacy Policy</a>.
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
            <a href="#" class="h6 link text-main">Help & FAQs</a>
          </li>
          <li class="br-line type-vertical"></li>
          <li>
            <a href="#" class="h6 link text-main">Factory</a>
          </li>
        </ul>
        <!-- <div class="list-hor flex-wrap">
          <span class="h6">Payment:</span>
          <ul class="payment-method-list">
            <li><img src="images/payment/visa-2.svg" alt="Payment"></li>
            <li><img src="images/payment/master-card-2.svg" alt="Payment"></li>
            <li><img src="images/payment/amex-2.svg" alt="Payment"></li>
            <li><img src="images/payment/discover-2.svg" alt="Payment"></li>
            <li><img src="images/payment/paypal-2.svg" alt="Payment"></li>
          </ul>
        </div> -->
        <!-- <div class="list-hor">
          <div class="tf-currencies">
            <select class="tf-dropdown-select style-default color-white-2 type-currencies">
              <option selected data-thumbnail="images/country/us.png">USD</option>
              <option data-thumbnail="images/country/vie.png">VND</option>
            </select>
          </div>
          <span class="br-line type-vertical"></span>
          <div class="tf-languages">
            <select class="tf-dropdown-select style-default color-white-2 type-languages">
              <option>English</option>
              <option>العربية</option>
              <option>简体中文</option>
              <option>اردو</option>
            </select>
          </div>
        </div> -->
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

      <li class="tf-languages">
        <a href="<?= $lango == 'ar' ? site_url('/') : site_url('/ar'); ?>"
          class="tf-btn-line style-white letter-space-0"><?= $lango == 'ar' ? 'English' : 'Arabic'; ?></a>
      </li>

    </ul>

  </div>
  <div class="canvas-body">
    <div class="mb-content-top">
      <!-- <ul class="nav-ul-mb" id="wrapper-menu-navigation"></ul> -->

      <ul class="nav-ul-mb">
        <li class="menu-item">
          <a href="javascript:void(0)" class="item-link"><?php echo $lango == 'ar' ? 'الرئيسية' : 'Home'; ?></a>
        </li>
        <li class="menu-item">
          <a href="<?= home_url('/faq'); ?>"
            class="item-link"><?php echo $lango == 'ar' ? 'الأسئلة الشائعة' : 'FAQ'; ?></a>
        </li>
        <li class="menu-item">
          <a href="<?= home_url('/contact-us'); ?>"
            class="item-link"><?php echo $lango == 'ar' ? 'اتصل بنا' : 'Contact'; ?></a>
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

<!-- Compare -->
<div class="offcanvas offcanvas-bottom canvas-compare" id="compare">
  <div class="canvas-wrapper">
    <div class="canvas-body">
      <div class="container">
        <div class="tf-compare-list wrap-empty_text">
          <div class="tf-compare-head">
            <h4 class="title"><?php echo $lango == 'ar' ? 'مقارنة المنتجات' : 'Compare products'; ?></h4>
          </div>

          <div class="tf-compare-offcanvas">
            <?php
            $ids = compare_get_ids();
            if (empty($ids)) {
              echo '<p class="box-text_empty h6 text-main">Your Compare is currently empty</p>';
            } else {
              foreach ($ids as $pid):
                $p = wc_get_product($pid);
                $img = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail'); ?>
                <div class="tf-compare-item" data-product-id="<?= esc_attr($pid); ?>">
                  <a href="<?= esc_url(get_permalink($pid)); ?>">
                    <div class="icon remove compare-remove-btn"><i class="icon-close"></i></div>
                    <img class="radius-3" src="<?= esc_url($img); ?>" alt="<?= esc_attr($p->get_name()); ?>">
                  </a>
                </div>
              <?php endforeach;
            } ?>
          </div>

          <div class="tf-compare-buttons" <?= empty($ids) ? 'style="display:none"' : ''; ?>>
            <a href="<?= site_url('/compare'); ?>"
              class="tf-btn bg-dark-2"><?php echo $lango == 'ar' ? 'مقارنة المنتجات' : 'Compare products'; ?></a>
            <button
              class="tf-btn btn-white line tf-compare-clear-all"><?php echo $lango == 'ar' ? 'مسح الكل' : 'Clear All'; ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /Compare -->

<!-- Quick View -->
<!-- <div class="modal modalCentered fade modal-quick-view" id="quickView">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content align-items-md-center">
      <i class="icon icon-close icon-close-popup" data-bs-dismiss="modal"></i>
      <div class="tf-product-media-wrap tf-btn-swiper-item">
        <div dir="ltr" class="swiper tf-single-slide">
          <div class="swiper-wrapper">

            <div class="swiper-slide" data-size="XS" data-color="beige">
              <div class="item">
                <img class="lazyload" data-src="images/products/cosmetic/product-5.jpg"
                  src="images/products/cosmetic/product-5.jpg" alt="">
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="tf-product-info-wrap">
        <div class="tf-product-info-inner tf-product-info-list">
          <div class="tf-product-info-heading">
            <a href="product-detail.html" class="link product-info-name fw-medium h1">
              Casual Round Neck T-Shirt
            </a>
            <div class="product-info-meta">
              <div class="rating">
                <div class="d-flex gap-4">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                      fill="#EF9122" />
                  </svg>
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                      fill="#EF9122" />
                  </svg>
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                      fill="#EF9122" />
                  </svg>
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                      fill="#EF9122" />
                  </svg>
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                      fill="#EF9122" />
                  </svg>
                </div>
                <div class="reviews text-main">(3.671 review)</div>
              </div>
              <div class="people-add text-primary">
                <i class="icon icon-shopping-cart-simple"></i>
                <span class="h6">9 people just added this product to their cart</span>
              </div>
            </div>
            <div class="product-info-price">
              <div class="price-wrap">
                <span class="price-new price-on-sale h2">$ 14.99</span>
                <span class="price-old compare-at-price h6">$ 24.99</span>
                <p class="badges-on-sale h6 fw-semibold">
                  <span class="number-sale" data-person-sale="29">
                    -29 %
                  </span>
                </p>
              </div>
            </div>
            <p class="product-infor-sub text-main h6">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse justo dolor, consectetur vel metus
              vitae,
              tincidunt finibus dui fusce tellus enim.
            </p>
          </div>
          <div class="tf-product-total-quantity w-100">
            <div class="group-btn">
              <div class="wg-quantity">
                <button class="btn-quantity btn-decrease">
                  <i class="icon icon-minus"></i>
                </button>
                <input class="quantity-product" type="text" name="number" value="1">
                <button class="btn-quantity btn-increase">
                  <i class="icon icon-plus"></i>
                </button>
              </div>
              <p class="h6 d-none d-sm-block">
                15 products available
              </p>
              <button type="button" class="d-sm-none hover-tooltip box-icon btn-add-wishlist flex-sm-shrink-0">
                <span class="icon icon-heart"></span>
                <span class="tooltip">Add to Wishlist</span>
              </button>
              <a href="#compare" data-bs-toggle="offcanvas"
                class="d-sm-none hover-tooltip tooltip-top box-icon flex-sm-shrink-0">
                <span class="icon icon-compare"></span>
                <span class="tooltip">Compare</span>
              </a>
            </div>
            <div class="group-btn flex-sm-nowrap">
              <a href="#shoppingCart" data-bs-toggle="offcanvas" class="tf-btn animate-btn btn-add-to-cart">
                ADD TO CART
                <i class="icon icon-shopping-cart-simple"></i>
              </a>
              <button type="button" class="d-none d-sm-flex hover-tooltip box-icon btn-add-wishlist flex-sm-shrink-0">
                <span class="icon icon-heart"></span>
                <span class="tooltip">Add to Wishlist</span>
              </button>
              <a href="#compare" data-bs-toggle="offcanvas"
                class="d-none d-sm-flex hover-tooltip tooltip-top box-icon flex-sm-shrink-0">
                <span class="icon icon-compare"></span>
                <span class="tooltip">Compare</span>
              </a>
            </div>
            <div class="group-btn">
              <a href="checkout.html" class="tf-btn btn-yellow w-100 animate-btn animate-dark">
                Pay with
                <span class="icon">
                  <svg width="68" height="18" viewBox="0 0 68 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M45.7745 0H40.609C40.3052 0 40.0013 0.30254 39.8494 0.605081L37.7224 13.9169C37.7224 14.2194 37.8743 14.3707 38.1782 14.3707H40.9129C41.2167 14.3707 41.3687 14.2194 41.3687 13.9169L41.9764 10.1351C41.9764 9.83258 42.2802 9.53004 42.736 9.53004H44.4072C47.9015 9.53004 49.8766 7.86606 50.3323 4.53811C50.6362 3.17668 50.3323 1.96652 49.7246 1.21017C48.8131 0.453813 47.4457 0 45.7745 0ZM46.3822 4.99193C46.0784 6.80717 44.711 6.80717 43.3437 6.80717H42.4321L43.0399 3.32795C43.0399 3.17668 43.1918 3.02541 43.4956 3.02541H43.7995C44.7111 3.02541 45.6226 3.02541 46.0784 3.63049C46.3822 3.78176 46.3822 4.23558 46.3822 4.99193Z"
                      fill="#139AD6" />
                    <path
                      d="M8.55188 0H3.38637C3.08251 0 2.77866 0.30254 2.62673 0.605081L0.499756 13.9169C0.499756 14.2194 0.651685 14.3707 0.955538 14.3707H3.38637C3.69022 14.3707 3.99407 14.0682 4.146 13.7656L4.75371 10.1351C4.75371 9.83258 5.05757 9.53004 5.51335 9.53004H7.18454C10.6789 9.53004 12.6539 7.86607 13.1097 4.53811C13.4135 3.17668 13.1097 1.96652 12.502 1.21017C11.5904 0.453813 10.375 0 8.55188 0ZM9.15959 4.99193C8.85574 6.80717 7.4884 6.80717 6.12105 6.80717H5.36142L5.96913 3.32795C5.96913 3.17668 6.12105 3.02541 6.42491 3.02541H6.72876C7.64032 3.02541 8.55188 3.02541 9.00766 3.63049C9.15959 3.78176 9.31152 4.23558 9.15959 4.99193ZM24.2004 4.84066H21.7695C21.6176 4.84066 21.3137 4.99193 21.3137 5.1432L21.1618 5.89955L21.0099 5.59701C20.4022 4.84066 19.3387 4.53811 18.1233 4.53811C15.3886 4.53811 12.9578 6.6559 12.502 9.53004C12.1981 11.0427 12.6539 12.4042 13.4135 13.3118C14.1732 14.2194 15.2367 14.522 16.604 14.522C18.8829 14.522 20.0983 13.1605 20.0983 13.1605L19.9464 13.9169C19.9464 14.2194 20.0983 14.3707 20.4022 14.3707H22.6811C22.9849 14.3707 23.2888 14.0682 23.4407 13.7656L24.8081 5.29447C24.6561 5.1432 24.3523 4.84066 24.2004 4.84066ZM20.706 9.68131C20.4022 11.0427 19.3387 12.1016 17.8194 12.1016C17.0598 12.1016 16.4521 11.7991 16.1482 11.4966C15.8444 11.0427 15.6924 10.4377 15.6924 9.68131C15.8444 8.31988 17.0598 7.26098 18.4271 7.26098C19.1868 7.26098 19.6425 7.56352 20.0983 7.86606C20.5541 8.31987 20.706 9.07623 20.706 9.68131Z"
                      fill="#263B80" />
                    <path
                      d="M61.2699 4.8416H58.839C58.6871 4.8416 58.3833 4.99287 58.3833 5.14414L58.2313 5.9005L58.0794 5.59796C57.4717 4.8416 56.4082 4.53906 55.1928 4.53906C52.4581 4.53906 50.0273 6.65685 49.5715 9.53099C49.2676 11.0437 49.7234 12.4051 50.4831 13.3128C51.2427 14.2204 52.3062 14.5229 53.6735 14.5229C55.9524 14.5229 57.1678 13.1615 57.1678 13.1615L57.0159 13.9178C57.0159 14.2204 57.1678 14.3716 57.4717 14.3716H59.7506C60.0545 14.3716 60.3583 14.0691 60.5102 13.7666L61.8776 5.29541C61.7256 5.14414 61.5737 4.8416 61.2699 4.8416ZM57.7755 9.68226C57.4717 11.0437 56.4082 12.1026 54.8889 12.1026C54.1293 12.1026 53.5216 11.8 53.2177 11.4975C52.9139 11.0437 52.762 10.4386 52.762 9.68226C52.9139 8.32082 54.1293 7.26193 55.4966 7.26193C56.2563 7.26193 56.7121 7.56447 57.1678 7.86701C57.7755 8.32082 57.9275 9.07718 57.7755 9.68226Z"
                      fill="#139AD6" />
                    <path
                      d="M37.4179 4.83984H34.8351C34.5312 4.83984 34.3793 4.99111 34.2274 5.14238L30.885 10.2856L29.3657 5.44493C29.2138 5.14238 29.0619 4.99111 28.6061 4.99111H26.1753C25.8714 4.99111 25.7195 5.29366 25.7195 5.5962L28.4542 13.6135L25.8714 17.244C25.7195 17.5466 25.8714 18.0004 26.1753 18.0004H28.6061C28.9099 18.0004 29.0619 17.8491 29.2138 17.6978L37.5698 5.74747C38.0256 5.29366 37.7217 4.83984 37.4179 4.83984Z"
                      fill="#263B80" />
                    <path
                      d="M64.158 0.455636L62.031 14.07C62.031 14.3725 62.1829 14.5238 62.4868 14.5238H64.6138C64.9176 14.5238 65.2215 14.2212 65.3734 13.9187L67.5004 0.606904C67.5004 0.304363 67.3484 0.153094 67.0446 0.153094H64.6138C64.4618 0.00182346 64.3099 0.153095 64.158 0.455636Z"
                      fill="#139AD6" />
                  </svg>
                </span>
              </a>
            </div>
            <div class="group-btn justify-content-center">
              <a href="#" class="tf-btn-line text-normal letter-space-0 fw-normal">
                More payment options
              </a>
            </div>
          </div>
          <a href="product-detail.html" class="tf-btn-line text-normal letter-space-0 fw-normal">
            <span class="h5">View full details</span>
            <i class="icon icon-arrow-top-right fs-24"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div> -->
<!-- /Quick View -->

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
  integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
  crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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