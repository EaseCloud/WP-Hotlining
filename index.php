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
                <?php do_settings_sections(WHL_DOMAIN); ?>
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
        ?>
        <input class="<?php echo $field_class; ?>"
               type="<?php echo $field_type;?>"
               id="<?php echo $field_name;?>"
               value="<?php form_option($field_name);?>"
               name="<?php echo $field_name;?>"
               aria-describedby="<?php echo $field_name;?>-description"
               <?php echo @$args['readonly'] ? 'readonly' : ''; ?>
            />
        <p class="description"
           id="<?php echo $field_name;?>-description"><?php
            echo $field_description;
            ?></p>
        <?php
    }

});

