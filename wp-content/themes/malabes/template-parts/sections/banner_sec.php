<?php
$lango = pll_current_language();
?>
<section>
    <div class="container">
        <div class="banner-V02 hover-img wow fadeInUp animated" style="visibility: visible; animation-name: fadeInUp;">
            <div class="banner_img img-style">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/banner/bannerV02.jpg" data-src="<?php echo get_template_directory_uri(); ?>/assets/images/banner/bannerV02.jpg" alt="Banner"
                    class=" ls-is-cached lazyloaded">
            </div>
            <div class="banner_content">
                <div class="box-text">
                    <h2 class="title type-semibold">
                        <a href="<?= site_url('shop'); ?>" class="text-primary"><?= $lango == 'ar' ? 'عرض اليوم' : 'Voucher Today'; ?></a>
                    </h2>
                    <h4 class="sub-title fw-bold"><?= $lango == 'ar' ? 'عرض اليوم' : 'Voucher Today'; ?> <span
                            class="text-primary"><?= $lango == 'ar' ? '150 ريال' : 'SAR 150'; ?></span></h4>
                </div>
                <div class="group-btn">
                    <a href="<?= site_url('shop'); ?>" class="tf-btn animate-btn type-small-3">
                        <?= $lango == 'ar' ? 'عرض اليوم' : 'Voucher Today'; ?>
                        <i class="icon icon-arrow-right"></i>
                    </a>
            
                </div>
            </div>
        </div>
    </div>
</section>