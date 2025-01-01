<?php


function static_web_plugin_settings_page() {

     //delete_option('static_web_plugin_settings');


    $settings = get_option('static_web_plugin_settings', [
        'global_background_color' => '',
        'global_text_color' => '',
        'user_defined_info_url' => '',
        'top_panel' => [
            'top_background_color' => '',
            'top_text_color' => '',
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '', 
            'links' => []
        ],
        'bottom_panel' => [
            'bottom_background_color' => '',
            'bottom_text_color' => '',
            'bottom_message' => '',
            'sections' => []
        ],


    ]);

    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];
    ?>
    <div class="wrap">
        <h1>Static Web Plugin Settings</h1>
        <!-- <h2>Top panel</h2> -->
        <!-- <span>Settings: <?php echo json_encode($settings, JSON_PRETTY_PRINT); ?></span> -->
        <script>
    console.log(<?php echo json_encode($settings); ?>);
</script>

        <form method="post" action="options.php">
        <?php
            // Outputs nonce, action, and option_page fields for the settings
            settings_fields('static_web_plugin_options_group');
            ?>

            <div>
                <label>Custom info link : </label>
                <input class="single-text-input" type="text" name="static_web_plugin_settings[user_defined_info_url]" value="<?php echo esc_url($settings['user_defined_info_url']); ?>" />
            </div>

            <div>
                <label for="main_background_color_field">Select global background color:</label>
                <input 
                    type="text" 
                    id="main_background_color_field" 
                    name="static_web_plugin_settings[global_background_color]" 
                    value="<?php echo esc_attr($settings['global_background_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div>
                <label for="main_text_color_field">Select global text color:</label>
                <input 
                    type="text" 
                    id="main_text_color_field" 
                    name="static_web_plugin_settings[global_text_color]" 
                    value="<?php echo esc_attr($settings['global_text_color']); ?>" 
                    class="my-color-field" 
                />
            </div>


            <h2>Top panel</h2>

            <div>
                <label>Main link: </label>
                <input class="single-text-input" type="text" name="static_web_plugin_settings[top_panel][main_link]" value="<?php echo esc_url($top_panel['main_link']); ?>" />
            </div>

            <label>Site name (optional): </label>
            <input class="single-text-input" type="text" name="static_web_plugin_settings[top_panel][main_title]" value="<?php echo esc_attr($top_panel['main_title']); ?>" />
            <div>
                <label for="static_web_plugin_settings[top_panel][logo_url]">Image URL:</label>
                <input type="text" id="image-url" name="static_web_plugin_settings[top_panel][logo_url]" value="<?php echo esc_url($top_panel['logo_url'] ?? ''); ?>" />
                <button type="button" id="select-image">Select Image</button>
            </div>

            <div>
                <label for="top_background_color_field">Select top bar background color:</label>
                <input 
                    type="text" 
                    id="top_background_color_field" 
                    name="static_web_plugin_settings[top_panel][top_background_color]" 
                    value="<?php echo esc_attr($top_panel['top_background_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div>
                <label for="top_text_color_field">Select top bar text color:</label>
                <input 
                    type="text" 
                    id="top_text_color_field" 
                    name="static_web_plugin_settings[top_panel][top_text_color]" 
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
                                <input type="text" name="static_web_plugin_settings[top_panel][links][<?php echo $link_index; ?>][text]" value="<?php echo esc_attr($link['text']); ?>" />
                                <label>Link URL: </label>
                                <input type="text" name="static_web_plugin_settings[top_panel][links][<?php echo $link_index; ?>][url]" value="<?php echo esc_url($link['url']); ?>" />

                                <button type="button" class="remove-link">Remove Link</button>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <button type="button" class="add-link">Add Link</button>
          
              
            </div>
            <h2>Bottom panel</h2>
            <div>
                <label for="bottom_background_color_field">Select bottom bar background color:</label>
                <input 
                    type="text" 
                    id="bottom_background_color_field" 
                    name="static_web_plugin_settings[bottom_panel][bottom_background_color]" 
                    value="<?php echo esc_attr($bottom_panel['bottom_background_color']); ?>" 
                    class="my-color-field" 
                />
            </div>

            <div>
                <label for="bottom_text_color_field">Select bottom bar text color:</label>
                <input 
                    type="text" 
                    id="bottom_text_color_field" 
                    name="static_web_plugin_settings[bottom_panel][bottom_text_color]" 
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
                            <input class="single-text-input" type="text" name="static_web_plugin_settings[bottom_panel][sections][<?php echo $section_index; ?>][title]" value="<?php echo esc_attr($section['title']); ?>" />
                            <div class="links">
                                <?php
                                if (!empty($section['links'])) {
                                    foreach ($section['links'] as $link_index => $link) {
                                        ?>
                                        <div class="link">
                                            <label>Link text: </label>
                                            <input type="text" name="static_web_plugin_settings[bottom_panel][sections][<?php echo $section_index; ?>][links][<?php echo $link_index; ?>][text]" value="<?php echo esc_attr($link['text']); ?>" />
                                            <label>Link URL: </label>
                                            <input type="text" name="static_web_plugin_settings[bottom_panel][sections][<?php echo $section_index; ?>][links][<?php echo $link_index; ?>][url]" value="<?php echo esc_url($link['url']); ?>" />
                                            <button type="button" class="remove-link">Remove Link</button>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <button type="button" class="add-link">Add Link</button>
                            <button type="button" class="remove-section">Remove Section</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" id="add-section">Add Section</button>
            <br/>
            <label>Bottom message (optional): </label>
            <input class="single-text-input" type="text" name="static_web_plugin_settings[bottom_panel][bottom_message]" value="<?php echo esc_attr($bottom_panel['bottom_message']); ?>" />
        
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function static_web_plugin_settings_init() {
    // register_setting('static_web_plugin_options_group', 'static_web_plugin_options');

    // add_settings_section(
    //     'static_web_plugin_section', 
    //     'Manage Sections', 
    //     'static_web_plugin_section_callback', 
    //     'static_web_plugin_settings'
    // );

    // add_settings_field(
    //     'static_web_plugin_field', 
    //     'Section and Links', 
    //     'static_web_plugin_field_callback', 
    //     'static_web_plugin_settings', 
    //     'static_web_plugin_section'
    // );

    // Register a single option for storing all settings
    register_setting(
        'static_web_plugin_options_group', // Option group
        'static_web_plugin_settings',      // Option name
        [
            'type' => 'string',
            'sanitize_callback' => 'static_web_plugin_sanitize_settings',
            'default' => json_encode([
                'global_background_color' => '',
                'global_text_color' => '',
                'user_defined_info_url' => '',
                'top_panel' => [
                    'top_background_color' => '',
                    'top_text_color' => '',
                    'main_link' => '',
                    'main_title' => '', 
                    'logo_url' => '', 
                    'links' => []
                ],
                'bottom_panel' => [
                    'bottom_background_color' => '',
                    'bottom_text_color' => '',
                    'bottom_message' => '',
                    'sections' => []],
            ])
        ]
    );
}

function static_web_plugin_sanitize_settings($input) {
    $sanitized = [];


    //Sanitize main object

    if(isset($input['global_background_color'])){
        $sanitized['global_background_color'] = sanitize_text_field($input['global_background_color']);
    }

    if(isset($input['global_text_color'])){
        $sanitized['global_text_color'] = sanitize_text_field($input['global_text_color']);
    }

    if(isset($input['user_defined_info_url'])){
        $sanitized['user_defined_info_url'] = sanitize_text_field($input['user_defined_info_url']);
    }


    // Sanitize top_panel
    if (isset($input['top_panel']) && is_array($input['top_panel'])) {
        $sanitized['top_panel'] = [
            'top_background_color' => isset($input['top_panel']['top_background_color']) ? sanitize_text_field($input['top_panel']['top_background_color']) : '',
            'top_text_color' => isset($input['top_panel']['top_text_color']) ? sanitize_text_field($input['top_panel']['top_text_color']) : '',
            'main_link' => isset($input['top_panel']['main_link']) ? esc_url_raw($input['top_panel']['main_link']) : '',
            'main_title' => isset($input['top_panel']['main_title']) ? sanitize_text_field($input['top_panel']['main_title']) : '',
            'logo_url' => isset($input['top_panel']['logo_url']) ? esc_url_raw($input['top_panel']['logo_url']) : '',
            'links' => []
        ];

        if (isset($input['top_panel']['links']) && is_array($input['top_panel']['links'])) {
            foreach ($input['top_panel']['links'] as $link) {
                if (is_array($link)) {
                    $sanitized['top_panel']['links'][] = [
                        'text' => isset($link['text']) ? sanitize_text_field($link['text']) : '',
                        'url' => isset($link['url']) ? esc_url_raw($link['url']) : ''
                    ];
                }
            }
        }
    }

    // Sanitize bottom_panel
    if (isset($input['bottom_panel']) && is_array($input['bottom_panel'])) {
        $sanitized['bottom_panel'] = [
            'bottom_background_color' => isset($input['bottom_panel']['bottom_background_color']) ? sanitize_text_field($input['bottom_panel']['bottom_background_color']) : '',
            'bottom_text_color' => isset($input['bottom_panel']['bottom_text_color']) ? sanitize_text_field($input['bottom_panel']['bottom_text_color']) : '',
            'bottom_message' =>  isset($input['bottom_panel']['bottom_message']) ? sanitize_text_field($input['bottom_panel']['bottom_message']) : '',
            'sections' => []];

        foreach ($input['bottom_panel']['sections'] as $section) {
            if (is_array($section)) {
                $sanitized_section = [
                    'title' => isset($section['title']) ? sanitize_text_field($section['title']) : '',
                    'links' => []
                ];

                if (isset($section['links']) && is_array($section['links'])) {
                    foreach ($section['links'] as $link) {
                        if (is_array($link)) {
                            $sanitized_section['links'][] = [
                                'text' => isset($link['text']) ? sanitize_text_field($link['text']) : '',
                                'url' => isset($link['url']) ? esc_url_raw($link['url']) : ''
                            ];
                        }
                    }
                }

                $sanitized['bottom_panel']['sections'][] = $sanitized_section;
            }
        }
    }

    return $sanitized;
}




// function static_web_plugin_section_callback() {
//     echo 'Enter the sections and links you want to include in the XML.';
// }

// function static_web_plugin_field_callback() {
//     $options = get_option('static_web_plugin_options');
//     // Example of output for adding multiple sections dynamically
//     echo '<textarea name="static_web_plugin_options[sections]" rows="10" cols="50">' . esc_textarea($options['sections']) . '</textarea>';
// }

function static_web_plugin_menu() {
    // Add a menu item to the sidebar
    add_menu_page(
        'Static Web Plugin Settings',          // Page title
        'Static Web Plugin',                   // Menu title
        'manage_options',              // Capability required
        'static_web_plugin_settings',          // Menu slug
        'static_web_plugin_settings_page',     // Callback function to render the settings page
        'dashicons-admin-generic',     // Icon (optional)
        100                            // Position in the menu
    );
}
add_action('admin_menu', 'static_web_plugin_menu');

add_action('admin_init', 'static_web_plugin_settings_init');



function static_web_plugin_enqueue_scripts($hook) {
    // Only enqueue on the settings page for the plugin
    if ($hook !== 'toplevel_page_static_web_plugin_settings') {
        return;
    }

    wp_enqueue_media(); // Enqueues the media uploader

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script(
        'static-web-plugin-admin',
        plugin_dir_url(__FILE__) . 'admin.js',
        [], // No dependencies
        null,
        true // Load in the footer
    );

    wp_enqueue_script(
        'my-plugin-color-picker',
        plugin_dir_url(__FILE__) . 'my-plugin-color-picker.js', // Your JS file
        ['wp-color-picker'], // Dependency for the color picker
        null,
        true
    );

    wp_enqueue_style(
        'static-web-plugin-admin-style',
        plugin_dir_url(__FILE__) . 'admin.css',
        [],
        null
    );
}
add_action('admin_enqueue_scripts', 'static_web_plugin_enqueue_scripts');


function allow_custom_url_schemes($protocols) {
    $protocols[] = 'sw';
    $protocols[] = 'sws';
    return $protocols;
}
add_filter('kses_allowed_protocols', 'allow_custom_url_schemes');