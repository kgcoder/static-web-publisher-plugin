<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_settings_page() {

     //delete_option('stwbpb_settings');


    $default_settings = array(
        'serve_hdoc_from_different_url' => false,
        'rewrite_prefix' => 'sw',
        'removal_selectors' => '',
        'side_panel_on_the_left' => false,
        'display_author_name' => false,
        'display_publish_date' => false,
        'comments_title' => '',
        'no_comments_message' => '',
        'top_panel' => array(
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '',
            'static_link' => false, 
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_message' => '',
            'sections' => array()
        ),
    );

    $existing_settings = get_option('stwbpb_settings', array());
    $settings = wp_parse_args($existing_settings, $default_settings);

    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];
    ?>
    <div class="wrap">
        <h1>Static Web Plugin Settings</h1>
        <form method="post" action="options.php">
        <?php
            // Outputs nonce, action, and option_page fields for the settings
            settings_fields('stwbpb_options_group');
            ?>

            <p class="red-text">Don't forget to click 'Save changes' button at the bottom of this page after changing the settings.</p>

            <h2>Main settings</h2>
            
            
            <div class="settings-option-div">
                <label>Serve HDOCs from a different page with prefix in URL (not recommended)</label>
                <div class="spacerW10"></div>
                <input id="serve-hdoc-checkbox" class="single-checkbox-input" type="checkbox" name="stwbpb_settings[serve_hdoc_from_different_url]" value="1" <?php echo !empty($settings['serve_hdoc_from_different_url']) ? 'checked' : ''; ?>/>
            </div>

           
            <div id="url-prefix-option" class="settings-option-div" style="display: <?php echo !empty($settings['serve_hdoc_from_different_url']) ? 'block' : 'none'; ?>;">
                <label>URL prefix: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[rewrite_prefix]" value="<?php echo esc_attr($settings['rewrite_prefix'] ?? 'sw'); ?>" />
            </div>
            
            <p id="prefix-description" class="prefix-description" style="display: <?php echo !empty($settings['serve_hdoc_from_different_url']) ? 'block' : 'none'; ?>;">
                After changing this prefix (and clicking Save Changes on this page!), go to 
                <a href="<?php echo admin_url('options-permalink.php'); ?>" target="_blank">
                    Settings → Permalinks
                </a> 
                and click <strong>Save Changes</strong> to update rewrite rules.
            </p>

            
            <div id="removal-selectors-option" class="settings-option-div" style="display: <?php echo empty($settings['serve_hdoc_from_different_url']) ? 'block' : 'none'; ?>;">
                <label>Elements to remove (specify selectors separated by commas): </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[removal_selectors]" value="<?php echo esc_attr($settings['removal_selectors']); ?>" />
            </div>
            
            <h2>Header info</h2>

            <div class="settings-option-div">
                <label>Display author's name</label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[display_author_name]" value="1" <?php echo !empty($settings['display_author_name']) ? 'checked' : ''; ?>/>
            </div>

            <div class="settings-option-div">
                <label>Display publish date</label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[display_publish_date]" value="1" <?php echo !empty($settings['display_publish_date']) ? 'checked' : ''; ?>/>
            </div>




            <h2>Comments</h2>

            <div class="settings-option-div">
                <label>Comments title: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[comments_title]" value="<?php echo esc_attr($settings['comments_title']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>No comments message: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[no_comments_message]" value="<?php echo esc_attr($settings['no_comments_message']); ?>" />
            </div>
            



            <h2>Top panel</h2>

            <div class="settings-option-div">
                <label>Main link: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[top_panel][main_link]" value="<?php echo esc_url($top_panel['main_link']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Is link static?</label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[top_panel][static_link]" value="1" <?php echo !empty($top_panel['static_link']) ? 'checked' : ''; ?>/>
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

            <p class="red-text">Use <code>http://OP</code> to add a link to the original page.</p>


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
                                <div class="settings-option-div">
                                    <label>Is link static?</label>
                                    <div class="spacerW10"></div>
                                    <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[top_panel][links][<?php echo esc_attr($link_index); ?>][static_link]" value="1" <?php echo !empty($link['static_link']) ? 'checked' : ''; ?>/>
                                </div>
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
            
            <p class="red-text">Use <code>http://OP</code> to add a link to the original page.</p>
          
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
                                            <div class="settings-option-div">
                                                <label>Is link static?</label>
                                                <div class="spacerW10"></div>
                                                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[bottom_panel][sections][<?php echo esc_attr($section_index); ?>][links][<?php echo esc_attr($link_index); ?>][static_link]" value="1" <?php echo !empty($link['static_link']) ? 'checked' : ''; ?>/>
                                            </div>
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
          
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}







function stwbpb_settings_init() {
   
    $default_settings = wp_json_encode(array(
        'serve_hdoc_from_different_url' => false,
        'rewrite_prefix' => 'sw',
        'removal_selectors' => '',
        'side_panel_on_the_left' => false,
        'display_author_name' => false,
        'display_publish_date' => false,
        'comments_title' => '',
        'no_comments_message' => '',
        'top_panel' => array(
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '',
            'static_link' => false,
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_message' => '',
            'sections' => array()
        ),
    ));


    // Register a single option for storing all settings
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



    //Sanitize main object


    if(isset($input['serve_hdoc_from_different_url'])){
        $sanitized['serve_hdoc_from_different_url'] = boolval($input['serve_hdoc_from_different_url']);
    }
    if(isset($input['side_panel_on_the_left'])){
        $sanitized['side_panel_on_the_left'] = boolval($input['side_panel_on_the_left']);
    }
    if(isset($input['display_author_name'])){
        $sanitized['display_author_name'] = boolval($input['display_author_name']);
    }
    if(isset($input['display_publish_date'])){
        $sanitized['display_publish_date'] = boolval($input['display_publish_date']);
    }

   $raw_prefix = isset($input['rewrite_prefix']) ? $input['rewrite_prefix'] : '';
    $prefix = sanitize_title_with_dashes($raw_prefix);
    if ($prefix === '') {
        $prefix = 'sw';
    }
    $sanitized['rewrite_prefix'] = $prefix;

    $sanitized['removal_selectors'] = isset($input['removal_selectors']) ? sanitize_text_field($input['removal_selectors']) : '';
    $sanitized['comments_title'] = isset($input['comments_title']) ? sanitize_text_field($input['comments_title']) : '';
    $sanitized['no_comments_message'] = isset($input['no_comments_message']) ? sanitize_text_field($input['no_comments_message']) : '';


    // Sanitize top_panel
    if (isset($input['top_panel']) && is_array($input['top_panel'])) {
        $sanitized['top_panel'] = array(
            'main_link' => isset($input['top_panel']['main_link']) ? esc_url_raw($input['top_panel']['main_link']) : '',
            'main_title' => isset($input['top_panel']['main_title']) ? sanitize_text_field($input['top_panel']['main_title']) : '',
            'logo_url' => isset($input['top_panel']['logo_url']) ? esc_url_raw($input['top_panel']['logo_url']) : '',
            'links' => array()
        );

        if(isset($input['top_panel']['static_link'])){
            $sanitized['top_panel']['static_link'] = boolval($input['top_panel']['static_link']);
        }


        if (isset($input['top_panel']['links']) && is_array($input['top_panel']['links'])) {
            foreach ($input['top_panel']['links'] as $link) {
                if (is_array($link)) {

                    $sanitizedStatic = !empty($link['static_link']) ? true : false;

                    $sanitized['top_panel']['links'][] = array(
                        'text' => isset($link['text']) ? sanitize_text_field($link['text']) : '',
                        'url' => isset($link['url']) ? esc_url_raw($link['url']) : '',
                        'static_link' => $sanitizedStatic
                    );
                }
            }
        }
    }

    // Sanitize bottom_panel
    if (isset($input['bottom_panel']) && is_array($input['bottom_panel'])) {
        $sanitized['bottom_panel'] = array(
            'bottom_message' =>  isset($input['bottom_panel']['bottom_message']) ? sanitize_text_field($input['bottom_panel']['bottom_message']) : '',
            'sections' => array()
        );

        if (isset($input['bottom_panel']['sections']) && is_array($input['bottom_panel']['sections'])) {
            foreach ($input['bottom_panel']['sections'] as $section) {
                if (is_array($section)) {
                    $sanitized_section = array(
                        'title' => isset($section['title']) ? sanitize_text_field($section['title']) : '',
                        'links' => array()
                    );

                    if (isset($section['links']) && is_array($section['links'])) {
                        foreach ($section['links'] as $link) {
                            if (is_array($link)) {
                                $sanitizedStatic = !empty($link['static_link']) ? true : false;

                                $sanitized_section['links'][] = array(
                                    'text' => isset($link['text']) ? sanitize_text_field($link['text']) : '',
                                    'url' => isset($link['url']) ? esc_url_raw($link['url']) : '',
                                    'static_link' => $sanitizedStatic
                                );
                            }
                        }
                    }

                    $sanitized['bottom_panel']['sections'][] = $sanitized_section;
                }
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

    wp_enqueue_script(
        'static-web-publisher-admin',
        plugin_dir_url(__FILE__) . 'admin.js',
        array(), // No dependencies
        filemtime(plugin_dir_path(__FILE__) . 'admin.js'),
        true // Load in the footer
    );

    

    wp_enqueue_style(
        'static-web-publisher-admin-style',
        plugin_dir_url(__FILE__) . 'admin.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'admin.css')
    );

   
}
add_action('admin_enqueue_scripts', 'stwbpb_enqueue_scripts');





// Flush when option is updated (compares old/new values)
add_action('updated_option', 'stwbpb_maybe_flush_rewrites', 10, 3);
function stwbpb_maybe_flush_rewrites($option, $old_value, $value) {
    if ($option !== 'stwbpb_settings') return;

    $old_prefix = isset($old_value['rewrite_prefix']) ? $old_value['rewrite_prefix'] : '';
    $new_prefix = isset($value['rewrite_prefix']) ? $value['rewrite_prefix'] : '';
    if ($old_prefix !== $new_prefix) {
        flush_rewrite_rules();
    }
}