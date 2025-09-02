<?php
namespace Glitch\Woo;

if (!defined('ABSPATH'))
    exit;

class ProductTypeConfigurator
{

    // ماب “بروفايلات” المنتج -> الأتريبيوتس والقيم
    private static $profiles = [
        'clothing' => [
            'label' => 'Clothing',
            'match_terms' => ['ملابس', 'clothes', 'fashion'],
            'attributes' => [
                [
                    'taxonomy' => 'pa_size',
                    'label' => 'Size',
                    'terms' => ['Small', 'Medium', 'Large', 'XL'],
                    'visible' => true,
                    'variation' => true,
                ],
                [
                    'taxonomy' => 'pa_weight',
                    'label' => 'Weight (g)',
                    'terms' => ['100g', '250g', '500g'],
                    'visible' => true,
                    'variation' => false,
                ],
            ],
        ],

        'electronics' => [
            'label' => 'Electronics',
            'match_terms' => ['إلكترونيات', 'electronics', 'mobiles'],
            'attributes' => [
                [
                    'taxonomy' => 'pa_storage',
                    'label' => 'Storage',
                    'terms' => ['64GB', '128GB', '256GB', '512GB'],
                    'visible' => true,
                    'variation' => true,
                ],
                [
                    'taxonomy' => 'pa_weight',
                    'label' => 'Weight (g)',
                    'terms' => ['200g', '300g', '400g'],
                    'visible' => true,
                    'variation' => false,
                ],
            ],
        ],

        'perfume' => [
            'label' => 'Perfume',
            'match_terms' => ['عطور', 'perfume', 'fragrance'],
            'attributes' => [
                [
                    'taxonomy' => 'pa_weight',
                    'label' => 'Volume (ml)',
                    'terms' => ['50ml', '75ml', '100ml'],
                    'visible' => true,
                    'variation' => true,
                ],
            ],
        ],
    ];

    public static function boot()
    {
        // تأكيد وجود الأتريبيوتس والـ terms
        add_action('init', array(__CLASS__, 'ensure_global_attributes'));

        // ميتا بروفايل + UI
        add_action('add_meta_boxes', array(__CLASS__, 'add_profile_metabox'));
        add_action('save_post_product', array(__CLASS__, 'save_profile_meta'), 10, 2);

        // تطبيق البروفايل عند الحفظ
        add_action('save_post_product', array(__CLASS__, 'apply_profile_on_save'), 20, 2);

        // سكريبت إدمن لتحديد البروفايل تلقائيًا عند تغيير التصنيفات
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_js'));
    }

    /** إنشاء/تسجيل الأتريبيوتس والتيرمز لو مش موجودة */
    public static function ensure_global_attributes()
    {
        if (!function_exists('wc_create_attribute'))
            return;

        $needed = array(
            'pa_size' => 'Size',
            'pa_storage' => 'Storage',
            'pa_weight' => 'Weight',
        );

        foreach ($needed as $slug => $label) {
            self::maybe_create_attribute($slug, $label);
        }

        // إنشاء التيرمز المذكورة في الماب
        foreach (self::$profiles as $profile) {
            if (empty($profile['attributes']))
                continue;
            foreach ($profile['attributes'] as $attr) {
                $tax = isset($attr['taxonomy']) ? $attr['taxonomy'] : '';
                if (!$tax || !taxonomy_exists($tax))
                    continue;

                $terms = isset($attr['terms']) ? (array) $attr['terms'] : array();
                foreach ($terms as $term_name) {
                    if (!term_exists($term_name, $tax)) {
                        wp_insert_term($term_name, $tax);
                    }
                }
            }
        }
    }

    /** أنشئ أتريبيوت عالمي لو مش موجود وسجل التاكسونومي بتاعه */
    private static function maybe_create_attribute($slug, $label)
    {
        global $wpdb;

        // attribute_name في الجدول = slug بدون pa_
        $attr = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
            substr($slug, 3)
        ));

        if (!$attr) {
            $result = wc_create_attribute(array(
                'slug' => substr($slug, 3),
                'name' => $label,
                'type' => 'select',
                'order_by' => 'menu_order',
                'has_archives' => false,
            ));
            if (!is_wp_error($result)) {
                register_taxonomy($slug, array('product'), array(
                    'hierarchical' => false,
                    'show_ui' => false,
                    'query_var' => false,
                    'rewrite' => false,
                ));
            }
        } else {
            if (!taxonomy_exists($slug)) {
                register_taxonomy($slug, array('product'), array(
                    'hierarchical' => false,
                    'show_ui' => false,
                    'query_var' => false,
                    'rewrite' => false,
                ));
            }
        }
    }

    /** ميتا بوكس اختيار/عرض البروفايل */
    public static function add_profile_metabox()
    {
        add_meta_box(
            'glitch_product_profile',
            __('Product Profile (Auto)', 'glitch'),
            array(__CLASS__, 'render_profile_metabox'),
            'product',
            'side',
            'high'
        );
    }

    public static function render_profile_metabox(\WP_Post $post)
    {
        $profile = get_post_meta($post->ID, '_product_profile', true);
        if (!$profile)
            $profile = '';

        wp_nonce_field('glitch_profile_nonce', 'glitch_profile_nonce');

        echo '<p style="margin:0 0 8px;">' . esc_html__('Detected / Selected Profile', 'glitch') . '</p>';
        echo '<select name="glitch_product_profile" id="glitch_product_profile" style="width:100%;">';
        echo '<option value="">' . esc_html__('- Auto -', 'glitch') . '</option>';

        foreach (self::$profiles as $key => $data) {
            echo '<option value="' . esc_attr($key) . '"' . selected($profile, $key, false) . '>' . esc_html($data['label']) . '</option>';
        }

        echo '</select>';
        echo '<p style="color:#666;margin-top:6px;">' . esc_html__('Auto-updates when you change product categories (before save).', 'glitch') . '</p>';
    }

    /** حفظ الميتا المختارة يدويًا */
    public static function save_profile_meta($post_id, \WP_Post $post)
    {
        if (!isset($_POST['glitch_profile_nonce']) || !wp_verify_nonce($_POST['glitch_profile_nonce'], 'glitch_profile_nonce'))
            return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if ($post->post_type !== 'product')
            return;

        $manual = isset($_POST['glitch_product_profile']) ? sanitize_text_field($_POST['glitch_product_profile']) : '';
        update_post_meta($post_id, '_product_profile', $manual);
    }

    /** حقن الأتريبيوتس المناسبة عند حفظ المنتج */
    public static function apply_profile_on_save($post_id, \WP_Post $post)
    {
        if ($post->post_type !== 'product')
            return;

        // حدّد البروفايل: يدويًا إن وجد، وإلا من التصنيفات
        $profile = get_post_meta($post_id, '_product_profile', true);
        if (!$profile) {
            $profile = self::detect_profile_from_terms($post_id);
            if ($profile)
                update_post_meta($post_id, '_product_profile', $profile);
        }
        if (!$profile || !isset(self::$profiles[$profile]))
            return;

        $product = wc_get_product($post_id);
        if (!$product)
            return;

        $attributes_data = $product->get_attributes();
        if (!is_array($attributes_data))
            $attributes_data = array();

        foreach (self::$profiles[$profile]['attributes'] as $attr) {
            $tax = isset($attr['taxonomy']) ? $attr['taxonomy'] : '';
            $label = isset($attr['label']) ? $attr['label'] : '';
            $terms = isset($attr['terms']) ? (array) $attr['terms'] : array();
            $visible = !empty($attr['visible']);
            $is_var = !empty($attr['variation']);

            if ($tax && taxonomy_exists($tax)) {
                // اربط التيرمز بالمنتج
                $term_slugs = array();
                foreach ($terms as $term_name) {
                    $term = get_term_by('name', $term_name, $tax);
                    if ($term && !is_wp_error($term)) {
                        $term_slugs[] = $term->slug;
                    }
                }
                if (!empty($term_slugs)) {
                    wp_set_object_terms($post_id, $term_slugs, $tax, true);
                }

                $attributes_data[$tax] = new \WC_Product_Attribute(array(
                    'id' => wc_attribute_taxonomy_id_by_name($tax),
                    'name' => $tax,
                    'options' => array_map('sanitize_title', $term_slugs),
                    'position' => 0,
                    'visible' => $visible,
                    'variation' => $is_var,
                ));
            } else {
                // كـ custom attribute (لو عايز تدخل قيم حرّة)
                if (!$label)
                    $label = 'Custom';
                $attributes_data[$label] = new \WC_Product_Attribute(array(
                    'id' => 0,
                    'name' => $label,
                    'options' => $terms,
                    'position' => 0,
                    'visible' => $visible,
                    'variation' => $is_var,
                ));
            }
        }

        $product->set_attributes($attributes_data);
        $product->save();
    }

    /** اكتشاف البروفايل من أسماء تصنيفات المنتج */
    private static function detect_profile_from_terms($post_id)
    {
        $terms = wp_get_post_terms($post_id, 'product_cat', array('fields' => 'names'));
        if (is_wp_error($terms))
            return null;

        $terms_lower = array();
        foreach ($terms as $t) {
            $terms_lower[] = function_exists('mb_strtolower') ? mb_strtolower($t) : strtolower($t);
        }

        foreach (self::$profiles as $key => $profile) {
            if (empty($profile['match_terms']))
                continue;
            foreach ($profile['match_terms'] as $needle) {
                $needle_low = function_exists('mb_strtolower') ? mb_strtolower($needle) : strtolower($needle);
                foreach ($terms_lower as $cat_name) {
                    if (strpos($cat_name, $needle_low) !== false) {
                        return $key;
                    }
                }
            }
        }
        return null;
    }

    /** تحميل سكريبت إدمن بسيط لاكتشاف البروفايل لحظيًا */
    public static function enqueue_admin_js($hook)
    {
        if ($hook !== 'post.php' && $hook !== 'post-new.php')
            return;
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'product')
            return;

        // بنحقنه داخل jquery core (موجود فعلًا في لوحة التحكم)
        wp_add_inline_script('jquery-core', self::admin_inline_js());
    }

    /** تحويل Array إلى تمثيل JS ['a','b'] */
    private static function js_array($arr)
    {
        $out = array_map(function ($v) {
            return "'" . esc_js($v) . "'";
        }, (array) $arr);
        return implode(',', $out);
    }

    /** الكود الجافاسكريبت المحقون في صفحة المنتج */
    private static function admin_inline_js()
    {
        $clothing = self::js_array(self::$profiles['clothing']['match_terms']);
        $electronics = self::js_array(self::$profiles['electronics']['match_terms']);
        $perfume = self::js_array(self::$profiles['perfume']['match_terms']);

        return <<<JS
jQuery(function(\$){
  function detectProfileFromCats(){
    var labels = [];
    \$('#product_catchecklist input[type=checkbox]:checked').each(function(){
      var label = \$(this).closest('li').find('label').first().text().trim().toLowerCase();
      if(label){ labels.push(label); }
    });

    function matchProfile(labels, map){
      for (var key in map){
        var terms = map[key];
        for (var i=0; i<terms.length; i++){
          var t = terms[i];
          var low = (t || '').toLowerCase();
          for (var j=0; j<labels.length; j++){
            if(labels[j].indexOf(low) !== -1) return key;
          }
        }
      }
      return '';
    }

    var mapping = {
      clothing: [{$clothing}],
      electronics: [{$electronics}],
      perfume: [{$perfume}]
    };

    var detected = matchProfile(labels, mapping);
    var \$select = \$('#glitch_product_profile');
    if(\$select.length && !\$select.val()){
      \$select.val(detected).trigger('change');
    }
  }

  detectProfileFromCats();
  \$('#product_catchecklist').on('change', 'input[type=checkbox]', detectProfileFromCats);
});
JS;
    }
}
