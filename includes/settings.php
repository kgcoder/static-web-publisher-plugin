<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_settings_page() {

     //delete_option('stwbpb_settings');


    $default_settings = array(
        'removal_selectors' => '',
        'page_author_name' => 'show',
        'page_publish_date' => 'show',
        'post_author_name' => 'show',
        'post_publish_date' => 'show',
        'comments_title'            => '',
        'no_comments_message'       => '',
        'reply_button_label'        => '',
        'leave_comment_label'       => '',
        'load_more_comments_label'  => '',
        'open_in_new_tab_label'     => '',
        'form_title'                => '',
        'replying_to_label'         => '',
        'commenting_on_label'       => '',
        'name_label'                => '',
        'email_label'               => '',
        'comment_label'             => '',
        'submit_button_label'       => '',
        'submitted_title'           => '',
        'thank_you_message'         => '',
        'awaiting_approval_message' => '',
        'error_security'            => '',
        'error_closed'              => '',
        'error_invalid_parent'      => '',
        'error_name_required'       => '',
        'error_invalid_email'       => '',
        'error_comment_required'    => '',
        'error_save_failed'         => '',
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
        'page_mode' => 'embedded_hdoc_forced',
        'post_mode' => 'embedded_hdoc_forced',
        'republishing_policy' => 'implicit_allow',
        'reader_ui_theme' => 'light',
        'reader_fontset' => 'default',
        'show_promotion_button' => false,
        'show_post_nav' => false,
        'post_sidebar' => 'none',
        'page_sidebar' => 'none',
    );

    $existing_settings = get_option('stwbpb_settings', array());
    $settings = wp_parse_args($existing_settings, $default_settings);

    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];
    $sidebar_variants = stwbpb_sidebar_variants_get_all();

    // Kept in sync manually with reader/Fonts.js's kFontRoleSets (id, label,
    // main.fontFamily, headers.fontFamily, headers.fontWeight only — the
    // preview-relevant subset). Fonts.js can't be imported directly here: it
    // pulls in Globals.js, which instantiates the full reader manager stack
    // at module-load time. If kFontRoleSets changes, update this array too.
    $reader_fontsets = array(
        array('id' => 'default',            'label' => 'Default (sans-serif)', 'body_font' => 'Arial, Helvetica, sans-serif',                   'heading_font' => '"Helvetica Neue", Arial, sans-serif',                      'heading_weight' => 600),
        array('id' => 'classic-serif',      'label' => 'Classic serif',        'body_font' => 'Georgia, "Times New Roman", serif',               'heading_font' => '"Helvetica Neue", Arial, sans-serif',                      'heading_weight' => 700),
        array('id' => 'comic',              'label' => 'Comic',                'body_font' => '"Comic Sans MS", "Comic Sans", cursive',          'heading_font' => '"Comic Sans MS", "Comic Sans", cursive',                   'heading_weight' => 700),
        array('id' => 'typewriter',         'label' => 'Typewriter',           'body_font' => '"Courier New", Courier, monospace',               'heading_font' => '"Courier New", Courier, monospace',                        'heading_weight' => 700),
        array('id' => 'poster',             'label' => 'Poster',               'body_font' => 'Verdana, Geneva, sans-serif',                     'heading_font' => 'Impact, Haettenschweiler, "Arial Narrow Bold", sans-serif', 'heading_weight' => null),
        array('id' => 'elegant-script',     'label' => 'Elegant script',       'body_font' => 'Garamond, "Book Antiqua", Palatino, serif',       'heading_font' => '"Brush Script MT", "Segoe Script", cursive',               'heading_weight' => null),
        array('id' => 'modern-minimal',     'label' => 'Modern minimal',       'body_font' => '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif', 'heading_font' => '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif',          'heading_weight' => 300),
        array('id' => 'geometric-editorial','label' => 'Geometric editorial',  'body_font' => 'Garamond, "Book Antiqua", Palatino, serif',       'heading_font' => '"Century Gothic", "Trebuchet MS", sans-serif',             'heading_weight' => 700),
        array('id' => 'retro-poster',       'label' => 'Retro poster',         'body_font' => 'Georgia, serif',                                  'heading_font' => '"Copperplate", "Copperplate Gothic Light", fantasy',       'heading_weight' => null),
        array('id' => 'terminal',           'label' => 'Terminal',             'body_font' => 'Consolas, "Lucida Console", Monaco, monospace',   'heading_font' => 'Consolas, "Lucida Console", Monaco, monospace',            'heading_weight' => 700),
    );
    ?>
    <div class="wrap">
        <h1>Reader's Web Publisher Plugin Settings</h1>
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
                    <option value="none" <?php selected($settings['page_mode'], 'none'); ?>>None (do not modify the page)</option>
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
                    <option value="none" <?php selected($settings['post_mode'], 'none'); ?>>None (do not modify the page)</option>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Republishing policy: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[republishing_policy]">
                    <option value="implicit_allow" <?php selected($settings['republishing_policy'], 'implicit_allow'); ?>>Implicitly allow (no tag)</option>
                    <option value="explicit_allow" <?php selected($settings['republishing_policy'], 'explicit_allow'); ?>>Explicitly allow</option>
                    <option value="prohibit" <?php selected($settings['republishing_policy'], 'prohibit'); ?>>Prohibit (do-not-republish)</option>
                </select>
            </div>
            <p class="description">This is the default for all posts and pages (individual posts can override it). Allowing republishing lets other Reader's Web sites host a stable copy of your content if the original page is moved or removed, so links to it don't break. "Implicitly allow" (the default) permits this without adding any tag to the document; "Explicitly allow" states the same permission in the document itself. You can revoke this permission at any time by asking a site hosting a copy to take it down. Choose "Prohibit" to opt out entirely.</p>
            <div style="height:20px"></div>
            <div class="settings-option-div">
                <label>Reader UI theme: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[reader_ui_theme]">
                    <?php
                    $reader_themes = array(
                        'light' => 'Light', 'dark' => 'Dark', 'sepia' => 'Sepia',
                        'lavender' => 'Lavender', 'matrix' => 'Matrix', 'mint' => 'Mint',
                        'navy' => 'Navy', 'ocean' => 'Ocean', 'olive' => 'Olive',
                        'rose' => 'Rose', 'slate' => 'Slate',
                    );
                    foreach ($reader_themes as $theme_id => $theme_label): ?>
                        <option value="<?php echo esc_attr($theme_id); ?>" <?php selected($settings['reader_ui_theme'], $theme_id); ?>><?php echo esc_html($theme_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Reader font set: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[reader_fontset]">
                    <?php foreach ($reader_fontsets as $fs): ?>
                        <option value="<?php echo esc_attr($fs['id']); ?>"
                            data-heading-font="<?php echo esc_attr($fs['heading_font']); ?>"
                            data-heading-weight="<?php echo esc_attr($fs['heading_weight'] !== null ? $fs['heading_weight'] : 'bold'); ?>"
                            data-body-font="<?php echo esc_attr($fs['body_font']); ?>"
                            <?php selected($settings['reader_fontset'], $fs['id']); ?>><?php echo esc_html($fs['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Help promote Reader's Web by including a promo popup</label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[show_promotion_button]" value="1" <?php echo !empty($settings['show_promotion_button']) ? 'checked' : ''; ?>/>
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

            <h2>Post navigation</h2>

            <div class="settings-option-div">
                <label>Show previous and next post links</label>
                <div class="spacerW10"></div>
                <input class="single-checkbox-input" type="checkbox" name="stwbpb_settings[show_post_nav]" value="1" <?php echo !empty($settings['show_post_nav']) ? 'checked' : ''; ?>/>
            </div>

            <h2>Sidebar</h2>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=stwbpb-sidebar-variants')); ?>">Manage Sidebar Variants</a></p>

            <div class="settings-option-div">
                <label>Post sidebar: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[post_sidebar]">
                    <option value="none" <?php selected($settings['post_sidebar'], 'none'); ?>>None</option>
                    <?php foreach ($sidebar_variants as $sv): ?>
                        <option value="<?php echo esc_attr($sv['id']); ?>" <?php selected($settings['post_sidebar'], $sv['id']); ?>><?php echo esc_html($sv['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="settings-option-div">
                <label>Page sidebar: </label>
                <div class="spacerW10"></div>
                <select name="stwbpb_settings[page_sidebar]">
                    <option value="none" <?php selected($settings['page_sidebar'], 'none'); ?>>None</option>
                    <?php foreach ($sidebar_variants as $sv): ?>
                        <option value="<?php echo esc_attr($sv['id']); ?>" <?php selected($settings['page_sidebar'], $sv['id']); ?>><?php echo esc_html($sv['title']); ?></option>
                    <?php endforeach; ?>
                </select>
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

            <div class="settings-option-div">
                <label>Reply button label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[reply_button_label]" value="<?php echo esc_attr($settings['reply_button_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Leave a comment label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[leave_comment_label]" value="<?php echo esc_attr($settings['leave_comment_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Load more comments button label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[load_more_comments_label]" value="<?php echo esc_attr($settings['load_more_comments_label']); ?>" />
            </div>
            <p class="description">Leave empty to hide the Load more button in the comments section shown below the document content; readers will then only see the first page of comments there.</p>

            <div class="settings-option-div">
                <label>Open in new tab button label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[open_in_new_tab_label]" value="<?php echo esc_attr($settings['open_in_new_tab_label']); ?>" />
            </div>
            <p class="description">Shown in place of the comments list for a connected document hosted on another site, since its comments cannot be loaded here. Leave empty to use the default text.</p>

            <h2>Comment form labels</h2>

            <div class="settings-option-div">
                <label>Form title / heading: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[form_title]" value="<?php echo esc_attr($settings['form_title']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Replying to label: <small>(use %s for the author name)</small></label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[replying_to_label]" value="<?php echo esc_attr($settings['replying_to_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Commenting on label: <small>(use %s for the post title)</small></label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[commenting_on_label]" value="<?php echo esc_attr($settings['commenting_on_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Name field label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[name_label]" value="<?php echo esc_attr($settings['name_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Email field label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[email_label]" value="<?php echo esc_attr($settings['email_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Comment field label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[comment_label]" value="<?php echo esc_attr($settings['comment_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Submit button label: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[submit_button_label]" value="<?php echo esc_attr($settings['submit_button_label']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Success page title: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[submitted_title]" value="<?php echo esc_attr($settings['submitted_title']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Thank you message: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[thank_you_message]" value="<?php echo esc_attr($settings['thank_you_message']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Awaiting approval message: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[awaiting_approval_message]" value="<?php echo esc_attr($settings['awaiting_approval_message']); ?>" />
            </div>

            <h2>Comment form error messages</h2>

            <div class="settings-option-div">
                <label>Security check failed: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_security]" value="<?php echo esc_attr($settings['error_security']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Commenting is closed: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_closed]" value="<?php echo esc_attr($settings['error_closed']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Invalid parent comment: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_invalid_parent]" value="<?php echo esc_attr($settings['error_invalid_parent']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Name required: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_name_required]" value="<?php echo esc_attr($settings['error_name_required']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Invalid email: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_invalid_email]" value="<?php echo esc_attr($settings['error_invalid_email']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Comment required: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_comment_required]" value="<?php echo esc_attr($settings['error_comment_required']); ?>" />
            </div>

            <div class="settings-option-div">
                <label>Could not save comment: </label>
                <div class="spacerW10"></div>
                <input class="single-text-input" type="text" name="stwbpb_settings[error_save_failed]" value="<?php echo esc_attr($settings['error_save_failed']); ?>" />
            </div>

          
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}







function stwbpb_settings_init() {
   
    $default_settings = wp_json_encode(array(
        'removal_selectors' => '',
        'page_author_name' => 'show',
        'page_publish_date' => 'show',
        'post_author_name' => 'show',
        'post_publish_date' => 'show',
        'comments_title'            => '',
        'no_comments_message'       => '',
        'reply_button_label'        => '',
        'leave_comment_label'       => '',
        'load_more_comments_label'  => '',
        'open_in_new_tab_label'     => '',
        'form_title'                => '',
        'replying_to_label'         => '',
        'commenting_on_label'       => '',
        'name_label'                => '',
        'email_label'               => '',
        'comment_label'             => '',
        'submit_button_label'       => '',
        'submitted_title'           => '',
        'thank_you_message'         => '',
        'awaiting_approval_message' => '',
        'error_security'            => '',
        'error_closed'              => '',
        'error_invalid_parent'      => '',
        'error_name_required'       => '',
        'error_invalid_email'       => '',
        'error_comment_required'    => '',
        'error_save_failed'         => '',
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
        'page_mode' => 'embedded_hdoc_forced',
        'post_mode' => 'embedded_hdoc_forced',
        'republishing_policy' => 'implicit_allow',
        'show_post_nav' => false,
        'post_sidebar' => array('sections' => array()),
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

    $sanitized['show_promotion_button'] = boolval($input['show_promotion_button'] ?? false);
    $sanitized['show_post_nav'] = boolval($input['show_post_nav'] ?? false);

    $sanitized['removal_selectors'] = isset($input['removal_selectors']) ? sanitize_text_field($input['removal_selectors']) : '';

    $allowed_vis = array('show', 'hide');
    $sanitized['page_author_name']  = isset($input['page_author_name'])  && in_array($input['page_author_name'],  $allowed_vis, true) ? $input['page_author_name']  : 'show';
    $sanitized['page_publish_date'] = isset($input['page_publish_date']) && in_array($input['page_publish_date'], $allowed_vis, true) ? $input['page_publish_date'] : 'show';
    $sanitized['post_author_name']  = isset($input['post_author_name'])  && in_array($input['post_author_name'],  $allowed_vis, true) ? $input['post_author_name']  : 'show';
    $sanitized['post_publish_date'] = isset($input['post_publish_date']) && in_array($input['post_publish_date'], $allowed_vis, true) ? $input['post_publish_date'] : 'show';
    $sanitized['comments_title']            = isset($input['comments_title'])            ? sanitize_text_field($input['comments_title'])            : '';
    $sanitized['no_comments_message']       = isset($input['no_comments_message'])       ? sanitize_text_field($input['no_comments_message'])       : '';
    $sanitized['reply_button_label']        = isset($input['reply_button_label'])        ? sanitize_text_field($input['reply_button_label'])        : '';
    $sanitized['leave_comment_label']       = isset($input['leave_comment_label'])       ? sanitize_text_field($input['leave_comment_label'])       : '';
    $sanitized['load_more_comments_label']  = isset($input['load_more_comments_label'])  ? sanitize_text_field($input['load_more_comments_label'])  : '';
    $sanitized['open_in_new_tab_label']     = isset($input['open_in_new_tab_label'])     ? sanitize_text_field($input['open_in_new_tab_label'])     : '';
    $sanitized['form_title']                = isset($input['form_title'])                ? sanitize_text_field($input['form_title'])                : '';
    $sanitized['replying_to_label']         = isset($input['replying_to_label'])         ? sanitize_text_field($input['replying_to_label'])         : '';
    $sanitized['commenting_on_label']       = isset($input['commenting_on_label'])       ? sanitize_text_field($input['commenting_on_label'])       : '';
    $sanitized['name_label']                = isset($input['name_label'])                ? sanitize_text_field($input['name_label'])                : '';
    $sanitized['email_label']               = isset($input['email_label'])               ? sanitize_text_field($input['email_label'])               : '';
    $sanitized['comment_label']             = isset($input['comment_label'])             ? sanitize_text_field($input['comment_label'])             : '';
    $sanitized['submit_button_label']       = isset($input['submit_button_label'])       ? sanitize_text_field($input['submit_button_label'])       : '';
    $sanitized['submitted_title']           = isset($input['submitted_title'])           ? sanitize_text_field($input['submitted_title'])           : '';
    $sanitized['thank_you_message']         = isset($input['thank_you_message'])         ? sanitize_text_field($input['thank_you_message'])         : '';
    $sanitized['awaiting_approval_message'] = isset($input['awaiting_approval_message']) ? sanitize_text_field($input['awaiting_approval_message']) : '';
    $sanitized['error_security']            = isset($input['error_security'])            ? sanitize_text_field($input['error_security'])            : '';
    $sanitized['error_closed']              = isset($input['error_closed'])              ? sanitize_text_field($input['error_closed'])              : '';
    $sanitized['error_invalid_parent']      = isset($input['error_invalid_parent'])      ? sanitize_text_field($input['error_invalid_parent'])      : '';
    $sanitized['error_name_required']       = isset($input['error_name_required'])       ? sanitize_text_field($input['error_name_required'])       : '';
    $sanitized['error_invalid_email']       = isset($input['error_invalid_email'])       ? sanitize_text_field($input['error_invalid_email'])       : '';
    $sanitized['error_comment_required']    = isset($input['error_comment_required'])    ? sanitize_text_field($input['error_comment_required'])    : '';
    $sanitized['error_save_failed']         = isset($input['error_save_failed'])         ? sanitize_text_field($input['error_save_failed'])         : '';

    $allowed_modes = array('embedded_hdoc_forced', 'embedded_hdoc', 'doc_in_reader', 'standalone_doc', 'none');
    $sanitized['page_mode'] = isset($input['page_mode']) && in_array($input['page_mode'], $allowed_modes, true) ? $input['page_mode'] : 'embedded_hdoc_forced';
    $sanitized['post_mode'] = isset($input['post_mode']) && in_array($input['post_mode'], $allowed_modes, true) ? $input['post_mode'] : 'embedded_hdoc_forced';

    $allowed_rep_policies = array('implicit_allow', 'explicit_allow', 'prohibit');
    $sanitized['republishing_policy'] = isset($input['republishing_policy']) && in_array($input['republishing_policy'], $allowed_rep_policies, true) ? $input['republishing_policy'] : 'implicit_allow';

    $allowed_themes = array('light', 'dark', 'sepia', 'lavender', 'matrix', 'mint', 'navy', 'ocean', 'olive', 'rose', 'slate');
    $sanitized['reader_ui_theme'] = isset($input['reader_ui_theme']) && in_array($input['reader_ui_theme'], $allowed_themes, true) ? $input['reader_ui_theme'] : 'light';

    $allowed_fontsets = array('default', 'classic-serif', 'comic', 'typewriter', 'poster', 'elegant-script', 'modern-minimal', 'geometric-editorial', 'retro-poster', 'terminal');
    $sanitized['reader_fontset'] = isset($input['reader_fontset']) && in_array($input['reader_fontset'], $allowed_fontsets, true) ? $input['reader_fontset'] : 'default';


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

    // Sanitize post_sidebar and page_sidebar (variant ID or 'none')
    $allowed_sidebar_values = array_merge(array('none'), array_column(stwbpb_sidebar_variants_get_all(), 'id'));
    $sanitized['post_sidebar'] = isset($input['post_sidebar']) && in_array($input['post_sidebar'], $allowed_sidebar_values, true)
        ? $input['post_sidebar'] : 'none';
    $sanitized['page_sidebar'] = isset($input['page_sidebar']) && in_array($input['page_sidebar'], $allowed_sidebar_values, true)
        ? $input['page_sidebar'] : 'none';

    return $sanitized;
}




function stwbpb_menu() {
    // Add a menu item to the sidebar
    add_menu_page(
        'Reader\'s Web Publisher Settings',          // Page title
        'Reader\'s Web Publisher',                   // Menu title
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
    $is_settings     = ($hook === 'toplevel_page_static_web_publisher_settings');
    $is_variant_edit = (strpos($hook, 'sidebar-variant-edit') !== false);

    if (!$is_settings && !$is_variant_edit) {
        return;
    }

    if ($is_settings) {
        wp_enqueue_media(); // Enqueues the media uploader

        // Enqueue the real reader theme stylesheets so the theme dropdown's
        // swatches can use the live --bg-main/--text-main custom properties
        // instead of duplicating hex colors.
        $plugin_root_url  = plugin_dir_url(dirname(__FILE__));
        $plugin_root_path = plugin_dir_path(dirname(__FILE__));
        $theme_files = glob($plugin_root_path . 'reader/themes/*.css');
        if ($theme_files) {
            foreach ($theme_files as $theme_file) {
                $theme_name = basename($theme_file, '.css');
                wp_enqueue_style(
                    'stwbpb-theme-preview-' . $theme_name,
                    $plugin_root_url . 'reader/themes/' . $theme_name . '.css',
                    array(),
                    filemtime($theme_file)
                );
            }
        }
    }

    wp_enqueue_script(
        'static-web-publisher-admin',
        plugin_dir_url(__FILE__) . 'admin.js',
        array(), // No dependencies
        filemtime(plugin_dir_path(__FILE__) . 'admin.js'),
        true // Load in the footer
    );

    if ($is_variant_edit) {
        wp_add_inline_script('static-web-publisher-admin', 'window.swpSidebarFieldPrefix = "stwbpb_variant";', 'before');
    }

    wp_enqueue_style(
        'static-web-publisher-admin-style',
        plugin_dir_url(__FILE__) . 'admin.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'admin.css')
    );
}
add_action('admin_enqueue_scripts', 'stwbpb_enqueue_scripts');