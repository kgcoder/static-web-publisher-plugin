<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_settings_page() {

     //delete_option('stwbpb_settings');


    $settings = get_option('stwbpb_settings', array(
        'global_background_color' => '',
        'global_text_color' => '',
        'info_link_variant' => 'none',
        'user_defined_info_url' => '',
        'side_panel_on_the_left' => false,
        'modify_internal_links' => false,
        'modify_external_links' => false,
        'top_panel' => array(
            'top_background_color' => '',
            'top_text_color' => '',
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '', 
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_background_color' => '',
            'bottom_text_color' => '',
            'bottom_message' => '',
            'sections' => array()
        ),


    ));

    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];
    ?>
    <div class="wrap">
        <h1>Static Web Plugin Settings</h1>
        <script>
    console.log(<?php echo json_encode($settings); ?>);
</script>

        <form method="post" action="options.php">
        <?php
            // Outputs nonce, action, and option_page fields for the settings
            settings_fields('stwbpb_options_group');
            ?>



            <div class="settings-option-div">
                <label>
                <input type="radio" name="stwbpb_settings[info_link_variant]" value="none" <?php checked($settings['info_link_variant'], 'none'); ?>>
                Don't use info link (<strong>Not recommended!</strong>)
                </label>
            </div>
            <div class="settings-option-div">
            <label>
                <input type="radio" name="stwbpb_settings[info_link_variant]" value="default" <?php checked($settings['info_link_variant'], 'default'); ?>>
                Use default info link (<?php echo "https://reinventingtheweb.com/how-to-use-sw-links/" ?>)
            </label>
            </div>
           
            <div class="settings-option-div">
                <label>
                    <input type="radio" name="stwbpb_settings[info_link_variant]" value="custom" <?php checked($settings['info_link_variant'], 'custom'); ?>>
                    Use custom info link
                </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[user_defined_info_url]" value="<?php echo esc_url($settings['user_defined_info_url']); ?>" />
            </div>

            <div class="settings-option-div">
                <label for="main_background_color_field">Select global background color:</label>
                <div class="spacerW10"></div>
                <input 
                    type="text" 
                    id="main_background_color_field" 
                    name="stwbpb_settings[global_background_color]" 
                    value="<?php echo esc_attr($settings['global_background_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div class="settings-option-div">
                <label for="main_text_color_field">Select global text color:</label>
                <div class="spacerW10"></div>
                <input 
                    type="text" 
                    id="main_text_color_field" 
                    name="stwbpb_settings[global_text_color]" 
                    value="<?php echo esc_attr($settings['global_text_color']); ?>" 
                    class="my-color-field" 
                />
            </div>


            <h2>Top panel</h2>

            <div class="settings-option-div">
                <label>Main link: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[top_panel][main_link]" value="<?php echo esc_url($top_panel['main_link']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Site name (optional): </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[top_panel][main_title]" value="<?php echo esc_attr($top_panel['main_title']); ?>" />
            </div>
            <div class="settings-option-div">
                <label for="stwbpb_settings[top_panel][logo_url]">Image URL:</label>
                <div class="spacerW10"></div>
                <input type="text" id="image-url" name="stwbpb_settings[top_panel][logo_url]" value="<?php echo esc_url(isset($top_panel['logo_url']) ? $top_panel['logo_url'] : ''); ?>" />
                <div class="spacerW10"></div>
                <button type="button" id="select-image">Select Image</button>
            </div>

            <div class="settings-option-div">
                <label for="top_background_color_field">Select top bar background color:</label>
                <div class="spacerW10"></div>
                <input 
                    type="text" 
                    id="top_background_color_field" 
                    name="stwbpb_settings[top_panel][top_background_color]" 
                    value="<?php echo esc_attr($top_panel['top_background_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div class="settings-option-div">
                <label for="top_text_color_field">Select top bar text color:</label>
                <div class="spacerW10"></div>
                <input 
                    type="text" 
                    id="top_text_color_field" 
                    name="stwbpb_settings[top_panel][top_text_color]" 
                    value="<?php echo esc_attr($top_panel['top_text_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div id="top-panel-links-container">
              
                <div class="links">
                    <?php
                    if (!empty($top_panel['links'])) {
                        foreach ($top_panel['links'] as $link_index => $link) {
                            ?>
                            <div class="link">
                                <label>Link text: </label>
                                <input type="text" name="stwbpb_settings[top_panel][links][<?php echo esc_attr($link_index); ?>][text]" value="<?php echo esc_attr($link['text']); ?>" />
                                <div class="spacerH10"></div>
                                <label>Link URL: </label>
                                <input type="text" name="stwbpb_settings[top_panel][links][<?php echo esc_attr($link_index); ?>][url]" value="<?php echo esc_url($link['url']); ?>" />
                                <div class="spacerH10"></div>
                                <button type="button" class="remove-link">Remove Link</button>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <button type="button" class="add-link">Add Link</button>
          
              
            </div>

            <h2>Side panel</h2>

            <div class="settings-option-div">
                <label>Side panel on the left? </label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[side_panel_on_the_left]" value="1" <?php echo !empty($settings['side_panel_on_the_left']) ? 'checked' : ''; ?>/>
            </div>

            <h2>Bottom panel</h2>
            <div class="settings-option-div">
                <label for="bottom_background_color_field">Select bottom bar background color:</label>
                <div class="spacerW10"></div>
                <input 
                    type="text" 
                    id="bottom_background_color_field" 
                    name="stwbpb_settings[bottom_panel][bottom_background_color]" 
                    value="<?php echo esc_attr($bottom_panel['bottom_background_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div class="settings-option-div">
                <label for="bottom_text_color_field">Select bottom bar text color:</label>
                <div class="spacerW10"></div>
                <input 
                    type="text" 
                    id="bottom_text_color_field" 
                    name="stwbpb_settings[bottom_panel][bottom_text_color]" 
                    value="<?php echo esc_attr($bottom_panel['bottom_text_color']); ?>" 
                    class="my-color-field" 
                />
            </div>
          
            <div id="sections-container">
                <?php
                if (!empty($bottom_panel['sections'])) {
                    foreach ($bottom_panel['sections'] as $section_index => $section) {
                        ?>
                        <div class="section">
                            <label>Section Title: </label>
                            <input class="single-text-input" type="text" name="stwbpb_settings[bottom_panel][sections][<?php echo esc_attr($section_index); ?>][title]" value="<?php echo esc_attr($section['title']); ?>" />
                            <div class="spacerH10"></div>
                            <div class="links">
                                <?php
                                if (!empty($section['links'])) {
                                    foreach ($section['links'] as $link_index => $link) {
                                        ?>
                                        <div class="link">
                                            <label>Link text: </label>
                                            <input type="text" name="stwbpb_settings[bottom_panel][sections][<?php echo esc_attr($section_index); ?>][links][<?php echo esc_attr($link_index); ?>][text]" value="<?php echo esc_attr($link['text']); ?>" />
                                            <div class="spacerH10"></div>

                                            <label style="margin-top:10px;">Link URL: </label>
                                            <input type="text" name="stwbpb_settings[bottom_panel][sections][<?php echo esc_attr($section_index); ?>][links][<?php echo esc_attr($link_index); ?>][url]" value="<?php echo isset($link['url']) ? esc_url($link['url']) : ''; ?>" />
                                            <div class="spacerH10"></div>
                                            <button style="margin-top:10px;" type="button" class="remove-link">Remove Link</button>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <button type="button" class="add-link">Add Link</button>
                            <div class="spacerH10"></div>
                            <button type="button" class="remove-section">Remove Section</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" id="add-section">Add Section</button>
            <div class="spacerH10"></div>

            <br/>
            <div class="settings-option-div">
                <label>Bottom message (optional): </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[bottom_panel][bottom_message]" value="<?php echo esc_attr($bottom_panel['bottom_message']); ?>" />
            </div>
            <div class="settings-option-div">
                <label>Modify internal links? </label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[modify_internal_links]" value="1" <?php echo !empty($settings['modify_internal_links']) ? 'checked' : ''; ?>/>
            </div>

            <div class="settings-option-div">
                <label>Modify external links? </label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[modify_external_links]" value="1" <?php echo !empty($settings['modify_external_links']) ? 'checked' : ''; ?>/>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}







function stwbpb_settings_init() {
   
    $default_settings = wp_json_encode(array(
        'global_background_color' => '',
        'global_text_color' => '',
        'info_link_variant' => 'none',
        'user_defined_info_url' => '',
        'side_panel_on_the_left' => false,
        'modify_internal_links' => false,
        'modify_external_links' => false,
        'top_panel' => array(
            'top_background_color' => '',
            'top_text_color' => '',
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '',
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_background_color' => '',
            'bottom_text_color' => '',
            'bottom_message' => '',
            'sections' => array()
        ),
    ));


    // Register a single option for storing all settings
    // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic
    register_setting(
        'stwbpb_options_group', // Option group
        'stwbpb_settings',      // Option name
        array(
            'type' => 'string',
            'sanitize_callback' => 'stwbpb_sanitize_settings',
            'default' => $default_settings
        )
    );
}

function stwbpb_sanitize_settings($input) {
    $sanitized = array();

    $valid_info_link_variants = array('none', 'default', 'custom');

    //Sanitize main object

    if(isset($input['global_background_color'])){
        $sanitized['global_background_color'] = sanitize_text_field($input['global_background_color']);
    }

    if(isset($input['global_text_color'])){
        $sanitized['global_text_color'] = sanitize_text_field($input['global_text_color']);
    }

    $sanitized['info_link_variant'] = in_array($input['info_link_variant'], $valid_info_link_variants, true) ? $input['info_link_variant'] : 'none';

    if(isset($input['user_defined_info_url'])){
        $sanitized['user_defined_info_url'] = sanitize_text_field($input['user_defined_info_url']);
    }

    if(isset($input['side_panel_on_the_left'])){
        $sanitized['side_panel_on_the_left'] = boolval($input['side_panel_on_the_left']);
    }
    if(isset($input['modify_internal_links'])){
        $sanitized['modify_internal_links'] = boolval($input['modify_internal_links']);
    }
    if(isset($input['modify_external_links'])){
        $sanitized['modify_external_links'] = boolval($input['modify_external_links']);
    }



    // Sanitize top_panel
    if (isset($input['top_panel']) && is_array($input['top_panel'])) {
        $sanitized['top_panel'] = array(
            'top_background_color' => isset($input['top_panel']['top_background_color']) ? sanitize_text_field($input['top_panel']['top_background_color']) : '',
            'top_text_color' => isset($input['top_panel']['top_text_color']) ? sanitize_text_field($input['top_panel']['top_text_color']) : '',
            'main_link' => isset($input['top_panel']['main_link']) ? esc_url_raw($input['top_panel']['main_link']) : '',
            'main_title' => isset($input['top_panel']['main_title']) ? sanitize_text_field($input['top_panel']['main_title']) : '',
            'logo_url' => isset($input['top_panel']['logo_url']) ? esc_url_raw($input['top_panel']['logo_url']) : '',
            'links' => array()
        );

        if (isset($input['top_panel']['links']) && is_array($input['top_panel']['links'])) {
            foreach ($input['top_panel']['links'] as $link) {
                if (is_array($link)) {
                    $sanitized['top_panel']['links'][] = array(
                        'text' => isset($link['text']) ? sanitize_text_field($link['text']) : '',
                        'url' => isset($link['url']) ? esc_url_raw($link['url']) : ''
                    );
                }
            }
        }
    }

    // Sanitize bottom_panel
    if (isset($input['bottom_panel']) && is_array($input['bottom_panel'])) {
        $sanitized['bottom_panel'] = array(
            'bottom_background_color' => isset($input['bottom_panel']['bottom_background_color']) ? sanitize_text_field($input['bottom_panel']['bottom_background_color']) : '',
            'bottom_text_color' => isset($input['bottom_panel']['bottom_text_color']) ? sanitize_text_field($input['bottom_panel']['bottom_text_color']) : '',
            'bottom_message' =>  isset($input['bottom_panel']['bottom_message']) ? sanitize_text_field($input['bottom_panel']['bottom_message']) : '',
            'sections' => array()
        );

        foreach ($input['bottom_panel']['sections'] as $section) {
            if (is_array($section)) {
                $sanitized_section = array(
                    'title' => isset($section['title']) ? sanitize_text_field($section['title']) : '',
                    'links' => array()
                );

                if (isset($section['links']) && is_array($section['links'])) {
                    foreach ($section['links'] as $link) {
                        if (is_array($link)) {
                            $sanitized_section['links'][] = array(
                                'text' => isset($link['text']) ? sanitize_text_field($link['text']) : '',
                                'url' => isset($link['url']) ? esc_url_raw($link['url']) : ''
                            );
                        }
                    }
                }

                $sanitized['bottom_panel']['sections'][] = $sanitized_section;
            }
        }
    }

    

    return $sanitized;
}




function stwbpb_menu() {
    // Add a menu item to the sidebar
    add_menu_page(
        'Static Web Publisher Settings',          // Page title
        'Static Web Publisher',                   // Menu title
        'manage_options',              // Capability required
        'static_web_publisher_settings',          // Menu slug
        'stwbpb_settings_page',     // Callback function to render the settings page
        'dashicons-admin-generic',     // Icon (optional)
        100                            // Position in the menu
    );
}
add_action('admin_menu', 'stwbpb_menu');

add_action('admin_init', 'stwbpb_settings_init');



function stwbpb_enqueue_scripts($hook) {
    // Only enqueue on the settings page for the plugin
    
    if ($hook !== 'toplevel_page_static_web_publisher_settings') {
        return;
    }

    wp_enqueue_media(); // Enqueues the media uploader

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script(
        'static-web-publisher-admin',
        plugin_dir_url(__FILE__) . 'admin.js',
        array(), // No dependencies
        filemtime(plugin_dir_path(__FILE__) . 'admin.js'),
        true // Load in the footer
    );

    wp_enqueue_script(
        'static-web-publisher-color-picker',
        plugin_dir_url(__FILE__) . 'color-picker.js', 
        array('wp-color-picker'), // Dependency for the color picker
        filemtime(plugin_dir_path(__FILE__) . 'color-picker.js'),
        true
    );

    wp_enqueue_style(
        'static-web-publisher-admin-style',
        plugin_dir_url(__FILE__) . 'admin.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'admin.css')
    );

   
}
add_action('admin_enqueue_scripts', 'stwbpb_enqueue_scripts');

function stwbpb_enqueue_comment_style($hook) {
    if(isset($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), '/sw-comments/') !== false){

        global $wp_styles;
        
        // Dequeue all styles
        foreach( $wp_styles->registered as $handle => $style ) {
            wp_dequeue_style( $handle );
        }


        wp_enqueue_style(
            'static-web-publisher-comments-style',
            plugin_dir_url(__FILE__) . 'comments.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'comments.css')
        );
      
    }   
}

add_action('wp_enqueue_scripts', 'stwbpb_enqueue_comment_style');

function stwbpb_allow_custom_url_schemes($protocols) {
    $protocols[] = 'sw';
    $protocols[] = 'sws';
    return $protocols;
}
add_filter('kses_allowed_protocols', 'stwbpb_allow_custom_url_schemes');