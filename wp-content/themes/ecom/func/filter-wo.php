<?php
/**
 * نظام ربط البنرز مع المنتجات
 * Banner Product Link System
 */

// 1. إنشاء Custom Post Type للبنرز
function create_banners_post_type()
{
    $labels = array(
        'name' => 'البنرز',
        'singular_name' => 'بنر',
        'menu_name' => 'البنرز',
        'add_new' => 'إضافة بنر جديد',
        'add_new_item' => 'إضافة بنر جديد',
        'edit_item' => 'تعديل البنر',
        'new_item' => 'بنر جديد',
        'view_item' => 'عرض البنر',
        'search_items' => 'البحث في البنرز',
        'not_found' => 'لم يتم العثور على بنرز',
        'not_found_in_trash' => 'لم يتم العثور على بنرز في المهملات',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'banners'),
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-format-image',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true,
    );

    register_post_type('banners', $args);
}
add_action('init', 'create_banners_post_type');

// 2. إضافة ACF Fields للبنرز
function add_banner_acf_fields()
{
    if (function_exists('acf_add_local_field_group')) {

        acf_add_local_field_group(array(
            'key' => 'group_banner_fields',
            'title' => 'إعدادات البنر',
            'fields' => array(
                // صورة البنر
                array(
                    'key' => 'field_banner_image',
                    'label' => 'صورة البنر',
                    'name' => 'banner_image',
                    'type' => 'image',
                    'instructions' => 'اختر صورة البنر',
                    'required' => 1,
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),

                // عنوان البنر
                array(
                    'key' => 'field_banner_title',
                    'label' => 'عنوان البنر',
                    'name' => 'banner_title',
                    'type' => 'text',
                    'instructions' => 'العنوان الذي سيظهر على البنر',
                    'required' => 0,
                    'placeholder' => 'عنوان البنر',
                ),

                // وصف البنر
                array(
                    'key' => 'field_banner_description',
                    'label' => 'وصف البنر',
                    'name' => 'banner_description',
                    'type' => 'textarea',
                    'instructions' => 'وصف مختصر للبنر',
                    'required' => 0,
                    'rows' => 3,
                ),

                // نوع الرابط
                array(
                    'key' => 'field_link_type',
                    'label' => 'نوع الرابط',
                    'name' => 'link_type',
                    'type' => 'radio',
                    'instructions' => 'اختر نوع الرابط للبنر',
                    'required' => 1,
                    'choices' => array(
                        'product' => 'ربط بمنتج',
                        'category' => 'ربط بفئة',
                        'custom' => 'رابط مخصص',
                        'no_link' => 'بدون رابط',
                    ),
                    'default_value' => 'product',
                    'layout' => 'vertical',
                ),

                // اختيار المنتج (يظهر فقط عند اختيار "ربط بمنتج")
                array(
                    'key' => 'field_linked_product',
                    'label' => 'المنتج المرتبط',
                    'name' => 'linked_product',
                    'type' => 'select',
                    'instructions' => 'اختر المنتج الذي تريد ربط البنر به',
                    'required' => 0,
                    'choices' => array(), // سيتم ملؤها ديناميكياً
                    'default_value' => '',
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 1,
                    'placeholder' => 'اختر منتج...',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_link_type',
                                'operator' => '==',
                                'value' => 'product',
                            ),
                        ),
                    ),
                ),

                // اختيار الفئة (يظهر فقط عند اختيار "ربط بفئة")
                array(
                    'key' => 'field_linked_category',
                    'label' => 'الفئة المرتبطة',
                    'name' => 'linked_category',
                    'type' => 'taxonomy',
                    'instructions' => 'اختر الفئة التي تريد ربط البنر بها',
                    'required' => 0,
                    'taxonomy' => 'product_cat',
                    'field_type' => 'select',
                    'allow_null' => 1,
                    'add_term' => 0,
                    'save_terms' => 0,
                    'load_terms' => 0,
                    'return_format' => 'id',
                    'multiple' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_link_type',
                                'operator' => '==',
                                'value' => 'category',
                            ),
                        ),
                    ),
                ),

                // رابط مخصص (يظهر فقط عند اختيار "رابط مخصص")
                array(
                    'key' => 'field_custom_link',
                    'label' => 'الرابط المخصص',
                    'name' => 'custom_link',
                    'type' => 'url',
                    'instructions' => 'أدخل الرابط المخصص',
                    'required' => 0,
                    'placeholder' => 'https://example.com',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_link_type',
                                'operator' => '==',
                                'value' => 'custom',
                            ),
                        ),
                    ),
                ),

                // نص الزر
                array(
                    'key' => 'field_button_text',
                    'label' => 'نص الزر',
                    'name' => 'button_text',
                    'type' => 'text',
                    'instructions' => 'النص الذي سيظهر على الزر',
                    'required' => 0,
                    'default_value' => 'تسوق الآن',
                    'placeholder' => 'تسوق الآن',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_link_type',
                                'operator' => '!=',
                                'value' => 'no_link',
                            ),
                        ),
                    ),
                ),

                // فتح الرابط في نافذة جديدة
                array(
                    'key' => 'field_open_new_tab',
                    'label' => 'فتح في نافذة جديدة',
                    'name' => 'open_new_tab',
                    'type' => 'true_false',
                    'instructions' => 'فتح الرابط في نافذة جديدة',
                    'required' => 0,
                    'message' => 'فتح الرابط في نافذة جديدة',
                    'default_value' => 0,
                    'ui' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_link_type',
                                'operator' => '!=',
                                'value' => 'no_link',
                            ),
                        ),
                    ),
                ),

                // ترتيب العرض
                array(
                    'key' => 'field_banner_order',
                    'label' => 'ترتيب العرض',
                    'name' => 'banner_order',
                    'type' => 'number',
                    'instructions' => 'ترتيب ظهور البنر (1 = الأول)',
                    'required' => 0,
                    'default_value' => 1,
                    'min' => 1,
                    'step' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'banners',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
    }
}
add_action('acf/init', 'add_banner_acf_fields');

// 3. ملء المنتجات المعروضة في السيل
function load_sale_products_for_acf($field)
{
    // إعادة تعيين الخيارات
    $field['choices'] = array();

    // الحصول على المنتجات المعروضة في السيل
    $sale_products = wc_get_product_ids_on_sale();

    if (!empty($sale_products)) {
        foreach ($sale_products as $product_id) {
            $product = wc_get_product($product_id);
            if ($product && $product->is_visible()) {
                $field['choices'][$product_id] = $product->get_name() . ' (#' . $product_id . ')';
            }
        }
    }

    // إذا لم توجد منتجات في السيل، عرض رسالة
    if (empty($field['choices'])) {
        $field['choices'] = array('' => 'لا توجد منتجات معروضة في السيل حالياً');
    }

    return $field;
}
add_filter('acf/load_field/name=linked_product', 'load_sale_products_for_acf');

// 4. دالة للحصول على رابط البنر
function get_banner_link($banner_id)
{
    $link_type = get_field('link_type', $banner_id);
    $link_url = '';
    $target = get_field('open_new_tab', $banner_id) ? '_blank' : '_self';

    switch ($link_type) {
        case 'product':
            $product_id = get_field('linked_product', $banner_id);
            if ($product_id) {
                $link_url = get_permalink($product_id);
            }
            break;

        case 'category':
            $category_id = get_field('linked_category', $banner_id);
            if ($category_id) {
                $link_url = get_term_link($category_id, 'product_cat');
            }
            break;

        case 'custom':
            $link_url = get_field('custom_link', $banner_id);
            break;

        case 'no_link':
        default:
            return null;
    }

    if (!empty($link_url)) {
        return array(
            'url' => $link_url,
            'target' => $target,
            'text' => get_field('button_text', $banner_id) ?: 'تسوق الآن'
        );
    }

    return null;
}

// 5. دالة لعرض البنر
function display_banner($banner_id, $classes = '')
{
    $banner_image = get_field('banner_image', $banner_id);
    $banner_title = get_field('banner_title', $banner_id);
    $banner_description = get_field('banner_description', $banner_id);
    $banner_link = get_banner_link($banner_id);

    if (!$banner_image)
        return;

    $banner_classes = 'banner-item ' . $classes;

    ob_start();
    ?>
    <div class="sb-banner hover-img" data-banner-id="<?php echo $banner_id; ?>">
        <?php if ($banner_title || $banner_description || $banner_link): ?>
            <a href="<?php echo esc_url($banner_link['url']); ?>" class="image img-style d-inline-flex">
                <img src="<?php echo esc_url($banner_image['url']); ?>" data-src="<?php echo esc_url($banner_image['url']); ?>"
                    alt="<?php echo esc_attr($banner_image['alt'] ?: $banner_title); ?>" class="lazyload">
            </a>
            <div class="content">
                <h5 class="sub-title text-primary"><?php echo esc_html($banner_description); ?></h5>
                <h2 class="fw-semibold title">
                    <a href="<?php echo esc_url($banner_link['url']); ?>" class="text-white link">
                        <?php echo esc_html($banner_title); ?>
                    </a>
                </h2>
                <a href="<?php echo esc_url($banner_link['url']); ?>" class="tf-btn btn-white animate-btn animate-dark">
                    <?php echo esc_html($banner_link['text']); ?>
                    <i class="icon icon-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}

// 6. Shortcode لعرض البنرز
function banners_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'count' => -1,
        'order' => 'ASC',
        'orderby' => 'meta_value_num',
        'meta_key' => 'banner_order',
        'class' => '',
    ), $atts);

    $query_args = array(
        'post_type' => 'banners',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['count']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'meta_key' => $atts['meta_key'],
    );

    $banners_query = new WP_Query($query_args);

    if (!$banners_query->have_posts()) {
        return '';
    }

    ob_start();
    ?>
    <div class="banners-container <?php echo esc_attr($atts['class']); ?>">
        <?php while ($banners_query->have_posts()):
            $banners_query->the_post(); ?>
            <?php echo display_banner(get_the_ID()); ?>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('banners', 'banners_shortcode');

// 7. دالة مساعدة للحصول على البنرز
function get_banners($args = array())
{
    $default_args = array(
        'post_type' => 'banners',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_key' => 'banner_order',
    );

    $args = wp_parse_args($args, $default_args);
    return new WP_Query($args);
}

// 8. Widget للبنرز (اختياري)
class Banner_Widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct(
            'banner_widget',
            'عرض البنرز',
            array('description' => 'عرض البنرز في الشريط الجانبي')
        );
    }

    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        $count = !empty($instance['count']) ? $instance['count'] : 3;

        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo do_shortcode('[banners count="' . $count . '"]');

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'البنرز';
        $count = !empty($instance['count']) ? $instance['count'] : 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">العنوان:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>">عدد البنرز:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>"
                name="<?php echo $this->get_field_name('count'); ?>" type="number" value="<?php echo esc_attr($count); ?>"
                min="1">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['count'] = (!empty($new_instance['count'])) ? intval($new_instance['count']) : 3;
        return $instance;
    }
}

// تسجيل الWidget
function register_banner_widget()
{
    register_widget('Banner_Widget');
}
add_action('widgets_init', 'register_banner_widget');


// Filter
function get_unique_product_attributes($field_name, $sub_fields = array(), $unique_key = '', $product_args = array()) {
    // إعدادات افتراضية للمنتجات
    $default_args = array(
        'status' => 'publish',
        'limit' => -1,
    );
    
    // دمج المعايير المخصصة مع الافتراضية
    $product_args = array_merge($default_args, $product_args);
    
    // إعدادات افتراضية للحقول الفرعية
    if (empty($sub_fields)) {
        $sub_fields = array('color_name', 'color_code', 'color_image');
    }
    
    // تحديد المفتاح الفريد
    if (empty($unique_key)) {
        $unique_key = $sub_fields[0]; // أول حقل كمفتاح افتراضي
    }
    
    $unique_attributes = array();
    $products = wc_get_products($product_args);

    foreach ($products as $product) {
        $product_id = $product->get_id();

        if (have_rows($field_name, $product_id)) {
            while (have_rows($field_name, $product_id)) {
                the_row();
                
                // جلب قيمة المفتاح الفريد
                $key_value = get_sub_field($unique_key);
                
                if (!empty($key_value)) {
                    $sanitized_key = sanitize_title($key_value);
                    
                    // إنشاء العنصر إذا لم يكن موجوداً
                    if (!isset($unique_attributes[$sanitized_key])) {
                        $unique_attributes[$sanitized_key] = array(
                            'count' => 0,
                            'product_ids' => array()
                        );
                        
                        // إضافة جميع الحقول الفرعية المطلوبة
                        foreach ($sub_fields as $field) {
                            $unique_attributes[$sanitized_key][$field] = get_sub_field($field);
                        }
                    }

                    // زيادة العدد وإضافة ID المنتج إذا لم يكن موجوداً
                    if (!in_array($product_id, $unique_attributes[$sanitized_key]['product_ids'])) {
                        $unique_attributes[$sanitized_key]['count']++;
                        $unique_attributes[$sanitized_key]['product_ids'][] = $product_id;
                    }
                }
            }
        }
    }

    return $unique_attributes;
}


// إضافة المناطق والمدن السعودية
function setup_saudi_regions_cities() {
    
    // تفعيل السعودية كدولة افتراضية
    add_filter('default_checkout_billing_country', function() {
        return 'SA';
    });
    
    add_filter('default_checkout_shipping_country', function() {
        return 'SA';
    });
    
    // إضافة المناطق السعودية
    add_filter('woocommerce_states', function($states) {
        $states['SA'] = array(
            'AR' => 'الرياض',
            'MK' => 'مكة المكرمة', 
            'MD' => 'المدينة المنورة',
            'QS' => 'القصيم',
            'ES' => 'المنطقة الشرقية',
            'AS' => 'عسير',
            'TB' => 'تبوك',
            'HA' => 'حائل',
            'NB' => 'الحدود الشمالية',
            'JZ' => 'جازان',
            'NJ' => 'نجران',
            'BH' => 'الباحة',
            'JF' => 'الجوف'
        );
        return $states;
    });
}
add_action('init', 'setup_saudi_regions_cities');

// دالة لإرجاع المدن حسب المنطقة
function get_saudi_cities_by_state($state_code) {
    $cities = array(
        'AR' => array( // الرياض
            'riyadh' => 'الرياض',
            'diriyah' => 'الدرعية',
            'kharj' => 'الخرج',
            'dawadmi' => 'الدوادمي',
            'majmaah' => 'المجمعة',
            'quwayiyah' => 'القويعية',
            'aflaj' => 'الأفلاج',
            'zulfi' => 'الزلفي',
            'shaqra' => 'شقراء',
            'rumah' => 'رماح'
        ),
        'MK' => array( // مكة المكرمة
            'makkah' => 'مكة المكرمة',
            'jeddah' => 'جدة',
            'taif' => 'الطائف',
            'rabigh' => 'رابغ',
            'khulais' => 'خليص',
            'qunfudah' => 'القنفذة',
            'laith' => 'الليث',
            'jumum' => 'الجموم',
            'khurmah' => 'الخرمة',
            'turubah' => 'تربة'
        ),
        'MD' => array( // المدينة المنورة
            'madinah' => 'المدينة المنورة',
            'yanbu' => 'ينبع',
            'mahd' => 'مهد الذهب',
            'ula' => 'العلا',
            'wadi_faraa' => 'وادي الفرع',
            'khaybar' => 'خيبر',
            'badr' => 'بدر',
            'henakiyah' => 'الحناكية'
        ),
        'QS' => array( // القصيم
            'buraydah' => 'بريدة',
            'unaizah' => 'عنيزة',
            'rass' => 'الرس',
            'bukayriyah' => 'البكيرية',
            'badayea' => 'البدائع',
            'riyadh_khabra' => 'رياض الخبراء',
            'uyun_jiwa' => 'عيون الجواء',
            'dhurma' => 'ضرما'
        ),
        'ES' => array( // المنطقة الشرقية
            'dammam' => 'الدمام',
            'khobar' => 'الخبر',
            'dhahran' => 'الظهران',
            'jubail' => 'الجبيل',
            'qatif' => 'القطيف',
            'ahsa' => 'الأحساء',
            'khafji' => 'الخفجي',
            'ras_tanura' => 'رأس تنورة',
            'safwa' => 'صفوى',
            'saihat' => 'سيهات'
        ),
        'AS' => array( // عسير
            'abha' => 'أبها',
            'khamis_mushayt' => 'خميس مشيط',
            'najran_asir' => 'نجران عسير',
            'bisha' => 'بيشة',
            'muhail' => 'محايل',
            'sarat_ubaidah' => 'سراة عبيدة',
            'rijal_almaa' => 'رجال ألمع',
            'balqarn' => 'بلقرن'
        ),
        'TB' => array( // تبوك
            'tabuk' => 'تبوك',
            'tayma' => 'تيماء',
            'duba' => 'ضباء',
            'wajh' => 'الوجه',
            'haql' => 'حقل',
            'bid' => 'البدع'
        ),
        'HA' => array( // حائل
            'hail' => 'حائل',
            'baqaa' => 'بقعاء',
            'ghazalah' => 'الغزالة',
            'shinan' => 'الشنان'
        ),
        'NB' => array( // الحدود الشمالية
            'arar' => 'عرعر',
            'rafha' => 'رفحاء',
            'turaif' => 'طريف'
        ),
        'JZ' => array( // جازان
            'jazan' => 'جازان',
            'sabya' => 'صبيا',
            'abu_arish' => 'أبو عريش',
            'samtah' => 'صامطة',
            'ahad_masarihah' => 'أحد المسارحة'
        ),
        'NJ' => array( // نجران
            'najran' => 'نجران',
            'sharurah' => 'شرورة',
            'hubuna' => 'حبونا'
        ),
        'BH' => array( // الباحة
            'baha' => 'الباحة',
            'baljurashi' => 'بلجرشي',
            'mandaq' => 'المندق',
            'qilwah' => 'قلوة'
        ),
        'JF' => array( // الجوف
            'sakaka' => 'سكاكا',
            'qurayyat' => 'القريات',
            'dumat_jandal' => 'دومة الجندل'
        )
    );
    
    return isset($cities[$state_code]) ? $cities[$state_code] : array();
}

// تخصيص حقول الـ checkout لإضافة حقل المدينة
function customize_checkout_fields_with_cities($fields) {
    
    // تخصيص حقول الفواتير
    $fields['billing']['billing_country'] = array(
        'label' => 'الدولة',
        'required' => true,
        'class' => array('form-row-wide', 'address-field', 'update_totals_on_change'),
        'autocomplete' => 'country',
        'priority' => 40,
        'type' => 'country'
    );
    
    $fields['billing']['billing_state'] = array(
        'label' => 'المنطقة',
        'required' => true,
        'class' => array('form-row-first', 'address-field', 'update_totals_on_change'),
        'validate' => array('state'),
        'autocomplete' => 'address-level1',
        'priority' => 50,
        'type' => 'state'
    );
    
    // إضافة حقل المدينة المخصص
    $fields['billing']['billing_city_custom'] = array(
        'label' => 'المدينة',
        'required' => true,
        'class' => array('form-row-last', 'address-field'),
        'autocomplete' => 'address-level2',
        'priority' => 55,
        'type' => 'select',
        'options' => array('' => 'اختر المدينة...')
    );
    
    // إخفاء حقل المدينة الافتراضي
    $fields['billing']['billing_city']['class'][] = 'hidden-city-field';
    $fields['billing']['billing_city']['required'] = false;
    
    // نفس الإعدادات للشحن
    $fields['shipping']['shipping_country'] = array(
        'label' => 'الدولة',
        'required' => true,
        'class' => array('form-row-wide', 'address-field', 'update_totals_on_change'),
        'autocomplete' => 'country',
        'priority' => 40,
        'type' => 'country'
    );
    
    $fields['shipping']['shipping_state'] = array(
        'label' => 'المنطقة',
        'required' => true,
        'class' => array('form-row-first', 'address-field', 'update_totals_on_change'),
        'validate' => array('state'),
        'autocomplete' => 'address-level1',
        'priority' => 50,
        'type' => 'state'
    );
    
    $fields['shipping']['shipping_city_custom'] = array(
        'label' => 'المدينة',
        'required' => true,
        'class' => array('form-row-last', 'address-field'),
        'autocomplete' => 'address-level2',
        'priority' => 55,
        'type' => 'select',
        'options' => array('' => 'اختر المدينة...')
    );
    
    $fields['shipping']['shipping_city']['class'][] = 'hidden-city-field';
    $fields['shipping']['shipping_city']['required'] = false;
    
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'customize_checkout_fields_with_cities');

// AJAX لتحديث المدن حسب المنطقة المختارة
function update_cities_by_state() {
    if (!wp_verify_nonce($_POST['nonce'], 'update_cities_nonce')) {
        wp_die('Security check failed');
    }
    
    $state = sanitize_text_field($_POST['state']);
    $field_type = sanitize_text_field($_POST['field_type']); // billing أو shipping
    
    $cities = get_saudi_cities_by_state($state);
    
    $options_html = '<option value="">اختر المدينة...</option>';
    foreach ($cities as $city_code => $city_name) {
        $options_html .= '<option value="' . esc_attr($city_code) . '">' . esc_html($city_name) . '</option>';
    }
    
    wp_send_json_success(array(
        'options' => $options_html,
        'field_type' => $field_type
    ));
}
add_action('wp_ajax_update_cities_by_state', 'update_cities_by_state');
add_action('wp_ajax_nopriv_update_cities_by_state', 'update_cities_by_state');

// نسخ قيمة المدينة المخصصة إلى حقل المدينة الافتراضي
function sync_custom_city_field($order_id) {
    $billing_city_custom = get_post_meta($order_id, '_billing_city_custom', true);
    $shipping_city_custom = get_post_meta($order_id, '_shipping_city_custom', true);
    
    if ($billing_city_custom) {
        $cities = get_saudi_cities_by_state(get_post_meta($order_id, '_billing_state', true));
        if (isset($cities[$billing_city_custom])) {
            update_post_meta($order_id, '_billing_city', $cities[$billing_city_custom]);
        }
    }
    
    if ($shipping_city_custom) {
        $cities = get_saudi_cities_by_state(get_post_meta($order_id, '_shipping_state', true));
        if (isset($cities[$shipping_city_custom])) {
            update_post_meta($order_id, '_shipping_city', $cities[$shipping_city_custom]);
        }
    }
}
add_action('woocommerce_checkout_order_processed', 'sync_custom_city_field');

// JavaScript لمعالجة حقول المناطق والمدن
function add_cities_handler_script() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            // تعيين القيم الافتراضية
            function setDefaultValues() {
                if (!$('#billing_country').val()) {
                    $('#billing_country').val('SA').trigger('change');
                }
                
                setTimeout(function() {
                    if (!$('#billing_state').val()) {
                        $('#billing_state').val('AR').trigger('change');
                    }
                }, 500);
            }
            
            setDefaultValues();
            
            // معالجة تغيير المنطقة لتحديث المدن
            $(document).on('change', '#billing_state, #shipping_state', function() {
                var $stateField = $(this);
                var state = $stateField.val();
                var fieldType = $stateField.attr('id').includes('shipping') ? 'shipping' : 'billing';
                var $cityField = $('#' + fieldType + '_city_custom');
                
                // مسح المدن الحالية
                $cityField.html('<option value="">جاري التحميل...</option>');
                
                if (state && state !== '') {
                    // طلب AJAX لجلب المدن
                    $.ajax({
                        url: wc_checkout_params.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'update_cities_by_state',
                            state: state,
                            field_type: fieldType,
                            nonce: '<?php echo wp_create_nonce("update_cities_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $cityField.html(response.data.options);
                            } else {
                                $cityField.html('<option value="">خطأ في تحميل المدن</option>');
                            }
                        },
                        error: function() {
                            $cityField.html('<option value="">خطأ في الاتصال</option>');
                        }
                    });
                } else {
                    $cityField.html('<option value="">اختر المنطقة أولاً</option>');
                }
                
                // تحديث الـ checkout
                $('body').trigger('update_checkout');
            });
            
            // معالجة تغيير المدينة
            $(document).on('change', '#billing_city_custom, #shipping_city_custom', function() {
                $('body').trigger('update_checkout');
            });
            
            // التحقق من الحقول المطلوبة قبل الإرسال
            $(document).on('click', '#place_order', function(e) {
                var errors = [];
                
                // التحقق من المنطقة والمدينة للفواتير
                if (!$('#billing_state').val()) {
                    errors.push('يرجى اختيار المنطقة');
                    $('#billing_state').addClass('woocommerce-invalid');
                }
                
                if (!$('#billing_city_custom').val()) {
                    errors.push('يرجى اختيار المدينة');
                    $('#billing_city_custom').addClass('woocommerce-invalid');
                }
                
                // التحقق من حقول الشحن إذا كانت مفعلة
                if ($('#ship-to-different-address-checkbox').is(':checked')) {
                    if (!$('#shipping_state').val()) {
                        errors.push('يرجى اختيار منطقة الشحن');
                        $('#shipping_state').addClass('woocommerce-invalid');
                    }
                    
                    if (!$('#shipping_city_custom').val()) {
                        errors.push('يرجى اختيار مدينة الشحن');
                        $('#shipping_city_custom').addClass('woocommerce-invalid');
                    }
                }
                
                if (errors.length > 0) {
                    e.preventDefault();
                    
                    // عرض الأخطاء
                    var errorHtml = '<div class="woocommerce-error" role="alert"><ul>';
                    $.each(errors, function(index, error) {
                        errorHtml += '<li>' + error + '</li>';
                    });
                    errorHtml += '</ul></div>';
                    
                    $('.woocommerce-error').remove();
                    $('.woocommerce-checkout').prepend(errorHtml);
                    
                    // انتقال إلى أعلى الصفحة
                    $('html, body').animate({
                        scrollTop: $('.woocommerce-error').offset().top - 100
                    }, 500);
                    
                    return false;
                }
            });
            
            // إزالة علامات الخطأ عند التصحيح
            $(document).on('change', '.woocommerce-invalid', function() {
                $(this).removeClass('woocommerce-invalid');
            });
            
        });
        </script>
    
        <?php
    }
}
add_action('wp_footer', 'add_cities_handler_script', 20);

// التحقق من صحة حقل المدينة المخصص
function validate_custom_city_field($data, $errors) {
    
    // التحقق من المدينة للفواتير
    if (empty($data['billing_city_custom'])) {
        $errors->add('billing', 'يرجى اختيار المدينة.');
    }
    
    // التحقق من المدينة للشحن إذا كان مختلف
    if (!empty($data['ship_to_different_address']) && empty($data['shipping_city_custom'])) {
        $errors->add('shipping', 'يرجى اختيار مدينة الشحن.');
    }
}
add_action('woocommerce_after_checkout_validation', 'validate_custom_city_field', 10, 2);

// حفظ حقل المدينة المخصص
function save_custom_city_field($order_id) {
    if (!empty($_POST['billing_city_custom'])) {
        update_post_meta($order_id, '_billing_city_custom', sanitize_text_field($_POST['billing_city_custom']));
    }
    
    if (!empty($_POST['shipping_city_custom'])) {
        update_post_meta($order_id, '_shipping_city_custom', sanitize_text_field($_POST['shipping_city_custom']));
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_custom_city_field');

// عرض المدينة في صفحة الطلب (Admin)
function display_custom_city_in_admin($order) {
    $billing_city_custom = get_post_meta($order->get_id(), '_billing_city_custom', true);
    $shipping_city_custom = get_post_meta($order->get_id(), '_shipping_city_custom', true);
    
    if ($billing_city_custom) {
        $billing_state = get_post_meta($order->get_id(), '_billing_state', true);
        $cities = get_saudi_cities_by_state($billing_state);
        if (isset($cities[$billing_city_custom])) {
            echo '<p><strong>مدينة الفواتير:</strong> ' . esc_html($cities[$billing_city_custom]) . '</p>';
        }
    }
    
    if ($shipping_city_custom) {
        $shipping_state = get_post_meta($order->get_id(), '_shipping_state', true);
        $cities = get_saudi_cities_by_state($shipping_state);
        if (isset($cities[$shipping_city_custom])) {
            echo '<p><strong>مدينة الشحن:</strong> ' . esc_html($cities[$shipping_city_custom]) . '</p>';
        }
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_city_in_admin');
add_action('woocommerce_admin_order_data_after_shipping_address', 'display_custom_city_in_admin');

require_once get_template_directory() . '/func/compare.php';
require_once get_template_directory() . '/func/desco.php';
