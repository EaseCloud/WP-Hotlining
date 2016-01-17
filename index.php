<?php
/*
Plugin Name: WP Hot-linking
Plugin URI:  https://github.com/EaseCloud/WP-Hotlinking
Description: Allowing hot-linking in WordPress site.
Version:     0.1
Author:      Huang Wenchao / EaseCloud Inc.
Author URI:  http://www.easecloud.cn/
License:     MIT
License URI: https://raw.githubusercontent.com/EaseCloud/WP-Hotlinking/master/LICENSE
Domain Path: null
Text Domain: wp_hot_linking
*/

define('WHL_DOMAIN', 'wp_hot_linking');
define('WHL_OPTION_PAGE', 'options-hot-linking');
define('WHL_VERSION', '0.1');

//var_dump(add_query_arg(array('action' => 'wechat_callback'), home_url('/wp-admin/admin-ajax.php')));

/**
 * 翻译支持
 */
add_action('plugins_loaded', function() {
    load_textdomain(WHL_DOMAIN, __DIR__.'/languages/zh_CN.mo');
});


// 下面步骤是建立一个配置页面,并且注册好配置项

/**
 * Step 1. Add admin menu for plugin settings
 */
add_action('admin_menu', function () {

    // 注册配置页面的渲染函数和信息
    add_options_page(
        __('Hot-linking', WHL_DOMAIN),              // page_title
        __('Hot-linking', WHL_DOMAIN),              // menu_title
        'manage_options',                           // capability
        WHL_OPTION_PAGE,                            // menu_slug
        'whl_options'                               // function (callback)
    );

    function whl_options() { ?>
        <div class="wrap">
            <h2><?php _e('Hot-linking Settings', WHL_DOMAIN); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields(WHL_DOMAIN); ?>
                <?php do_settings_sections(WHL_OPTION_PAGE); ?>
                <?php submit_button(); ?>
            </form>
        </div><?php
    }

});

/**
 * Step 2. Register the plugin settings.
 * Referring: https://codex.wordpress.org/Settings_API
 */
add_action('admin_init', function() {

    add_settings_section(
        'section-basic',                            // id
        __('Basic Settings', WHL_DOMAIN),           // title
        function() {                                // callback
            _e('Set the hot-linking information here', WHL_DOMAIN);
        },
        WHL_OPTION_PAGE                             // page
    );

    // 预留高级选项卡
//    add_settings_section(
//        'section-advanced',                         // id
//        __('Advanced Settings', WXD),       // title
//        function() {},                              // callback
//        'options-wechat'                            // page
//    );

    $setting_fields = array(
        'whl_domains' => array(
            'title' => 'Domains',
            'type' => 'textarea',
            'class' => 'regular-text code',
            'description' => 'Mark the Hot-linking domains.',
        ),
    );

    foreach($setting_fields as $field_name => $args) {

        register_setting(WHL_DOMAIN, $field_name);

        // Referring: https://codex.wordpress.org/Function_Reference/add_settings_field
        add_settings_field(
            $field_name,                            // id
            @$args['title'] ?: $field_name,         // title
            'field_renderer',                       // callback
            WHL_OPTION_PAGE,                        // page
            'section-basic',                        // section
            array_merge(array(
                'field_name' => $field_name,
                'label_for' => $field_name,
            ), $args)                               // $args
        );

        // TODO: 还有 Sanitization 和 Validation 可以完善

    }

    function field_renderer($args) {
        $field_name = $args['field_name'];
        $field_title = @$args['title'] ?: $field_name;
        $field_type = @$args['type'] ?: 'text';
        $field_class = @$args['class'] ?: 'regular-text ltr';
        $field_description = @$args['description'] ?: '';
        if($field_type == 'textarea') { ?>
            <textarea class="<?php echo $field_class; ?>"
                   type="<?php echo $field_type;?>"
                   id="<?php echo $field_name;?>"
                   name="<?php echo $field_name;?>"
                   rows="8"
                   aria-describedby="<?php echo $field_name;?>-description"
                <?php echo @$args['readonly'] ? 'readonly' : ''; ?>
            ><?php form_option($field_name);?></textarea>
        <?php } elseif($field_type == 'text') { ?>
            <input class="<?php echo $field_class; ?>"
                   type="<?php echo $field_type;?>"
                   id="<?php echo $field_name;?>"
                   value="<?php form_option($field_name);?>"
                   name="<?php echo $field_name;?>"
                   aria-describedby="<?php echo $field_name;?>-description"
                <?php echo @$args['readonly'] ? 'readonly' : ''; ?>
            />
        <?php }?>
        <p class="description"
           id="<?php echo $field_name;?>-description"><?php
            echo $field_description;
            ?></p>
        <?php
    }

});


/**
 * 加入前端的替换链接
 */
add_action('wp_enqueue_scripts', function() {
    $hash = md5(get_option('whl_domains'));
    wp_enqueue_script(
        'wp-hotlinking',                                // handle
        plugin_dir_url(__FILE__)
            ."js/hotlinking.js?hash=$hash",            // source
        array('jquery'),                                // dependence
        WHL_VERSION,                                    // version
        true                                            // in_footer
    );
});


add_action('wp_ajax_hotlink_img', 'wp_ajax_hotlink_img_callback');
add_action('wp_ajax_nopriv_hotlink_img', 'wp_ajax_hotlink_img_callback');
function wp_ajax_hotlink_img_callback() {
    $url = $_GET['url'];
    if(preg_match('/^https?:\\/\\//', $url)) {
//        $result = (file_get_contents($url));
//        exit($result);
        exit(grab_image($url));
    }
}

function grab_image($url){
    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $raw=curl_exec($ch);
    curl_close ($ch);
    return $raw;
}


