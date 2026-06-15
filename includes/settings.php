<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_settings_page() {

     //delete_option('stwbpb_settings');


    $default_settings = array(
        'removal_selectors' => '',
        'side_panel_on_the_left' => false,
        'page_author_name' => 'show',
        'page_publish_date' => 'show',
        'post_author_name' => 'show',
        'post_publish_date' => 'show',
        'comments_title' => '',
        'no_comments_message' => '',
        'top_panel' => array(
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '',
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_message' => '',
            'sections' => array()
        ),
        'page_mode' => 'embedded_hdoc',
        'post_mode' => 'embedded_hdoc',
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
   
            <div id="removal-selectors-option" class="settings-option-div" style="display:block;">
                <label>Elements to remove (specify selectors separated by commas): </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[removal_selectors]" value="<?php echo esc_attr($settings['removal_selectors']); ?>" />
            </div>
            
            <div class="settings-option-div">
                <label>Page mode: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[page_mode]">
                    <option value="embedded_hdoc_forced" <?php selected($settings['page_mode'], 'embedded_hdoc_forced'); ?>>Embedded HDOC (forced)</option>
                    <option value="embedded_hdoc" <?php selected($settings['page_mode'], 'embedded_hdoc'); ?>>Embedded HDOC</option>
                    <option value="doc_in_reader" <?php selected($settings['page_mode'], 'doc_in_reader'); ?>>Reader UI</option>
                    <option value="standalone_doc" <?php selected($settings['page_mode'], 'standalone_doc'); ?>>Standalone document</option>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Post mode: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[post_mode]">
                    <option value="embedded_hdoc_forced" <?php selected($settings['post_mode'], 'embedded_hdoc_forced'); ?>>Embedded HDOC (forced)</option>
                    <option value="embedded_hdoc" <?php selected($settings['post_mode'], 'embedded_hdoc'); ?>>Embedded HDOC</option>
                    <option value="doc_in_reader" <?php selected($settings['post_mode'], 'doc_in_reader'); ?>>Reader UI</option>
                    <option value="standalone_doc" <?php selected($settings['post_mode'], 'standalone_doc'); ?>>Standalone document</option>
                </select>
            </div>

            <h2>Header info (pages)</h2>

            <div class="settings-option-div">
                <label>Author's name: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[page_author_name]">
                    <option value="show" <?php selected($settings['page_author_name'], 'show'); ?>>Show</option>
                    <option value="hide" <?php selected($settings['page_author_name'], 'hide'); ?>>Hide</option>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Publish date: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[page_publish_date]">
                    <option value="show" <?php selected($settings['page_publish_date'], 'show'); ?>>Show</option>
                    <option value="hide" <?php selected($settings['page_publish_date'], 'hide'); ?>>Hide</option>
                </select>
            </div>

            <h2>Header info (posts)</h2>

            <div class="settings-option-div">
                <label>Author's name: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[post_author_name]">
                    <option value="show" <?php selected($settings['post_author_name'], 'show'); ?>>Show</option>
                    <option value="hide" <?php selected($settings['post_author_name'], 'hide'); ?>>Hide</option>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Publish date: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[post_publish_date]">
                    <option value="show" <?php selected($settings['post_publish_date'], 'show'); ?>>Show</option>
                    <option value="hide" <?php selected($settings['post_publish_date'], 'hide'); ?>>Hide</option>
                </select>
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
          
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}







function stwbpb_settings_init() {
   
    $default_settings = wp_json_encode(array(
        'removal_selectors' => '',
        'side_panel_on_the_left' => false,
        'page_author_name' => 'show',
        'page_publish_date' => 'show',
        'post_author_name' => 'show',
        'post_publish_date' => 'show',
        'comments_title' => '',
        'no_comments_message' => '',
        'top_panel' => array(
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '',
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_message' => '',
            'sections' => array()
        ),
        'page_mode' => 'embedded_hdoc',
        'post_mode' => 'embedded_hdoc',
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

    if(isset($input['side_panel_on_the_left'])){
        $sanitized['side_panel_on_the_left'] = boolval($input['side_panel_on_the_left']);
    }

    $sanitized['removal_selectors'] = isset($input['removal_selectors']) ? sanitize_text_field($input['removal_selectors']) : '';

    $allowed_vis = array('show', 'hide');
    $sanitized['page_author_name']  = isset($input['page_author_name'])  && in_array($input['page_author_name'],  $allowed_vis, true) ? $input['page_author_name']  : 'show';
    $sanitized['page_publish_date'] = isset($input['page_publish_date']) && in_array($input['page_publish_date'], $allowed_vis, true) ? $input['page_publish_date'] : 'show';
    $sanitized['post_author_name']  = isset($input['post_author_name'])  && in_array($input['post_author_name'],  $allowed_vis, true) ? $input['post_author_name']  : 'show';
    $sanitized['post_publish_date'] = isset($input['post_publish_date']) && in_array($input['post_publish_date'], $allowed_vis, true) ? $input['post_publish_date'] : 'show';
    $sanitized['comments_title'] = isset($input['comments_title']) ? sanitize_text_field($input['comments_title']) : '';
    $sanitized['no_comments_message'] = isset($input['no_comments_message']) ? sanitize_text_field($input['no_comments_message']) : '';

    $allowed_modes = array('embedded_hdoc_forced', 'embedded_hdoc', 'doc_in_reader', 'standalone_doc');
    $sanitized['page_mode'] = isset($input['page_mode']) && in_array($input['page_mode'], $allowed_modes, true) ? $input['page_mode'] : 'embedded_hdoc_forced';
    $sanitized['post_mode'] = isset($input['post_mode']) && in_array($input['post_mode'], $allowed_modes, true) ? $input['post_mode'] : 'embedded_hdoc_forced';


    // Sanitize top_panel
    if (isset($input['top_panel']) && is_array($input['top_panel'])) {
        $sanitized['top_panel'] = array(
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