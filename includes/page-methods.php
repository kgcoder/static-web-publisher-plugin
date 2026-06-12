<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function stwbpb_custom_post_endpoints_add_meta_box() {
    add_meta_box(
        'custom_post_endpoints_meta_box',
        'Static Web Publisher Settings',
        'stwbpb_custom_post_endpoints_meta_box_callback',
        array('post', 'page'), // Enable for posts and pages
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'stwbpb_custom_post_endpoints_add_meta_box');

function stwbpb_custom_post_endpoints_meta_box_callback($post) {
    $connections_info  = get_post_meta($post->ID, '_static_web_connections_info', true);
    $mode_value        = get_post_meta($post->ID, '_hdoc_display_mode', true) ?: 'default';
    $author_display    = get_post_meta($post->ID, '_hdoc_author_name_display', true) ?: 'default';
    $date_display      = get_post_meta($post->ID, '_hdoc_publish_date_display', true) ?: 'default';

    wp_nonce_field('custom_post_endpoints_meta_box_nonce', 'custom_post_endpoints_nonce');
    ?>
    <label for="hdoc_display_mode"><strong>Display mode:</strong></label><br>
    <select name="hdoc_display_mode" id="hdoc_display_mode" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($mode_value, 'default'); ?>>Default (use global setting)</option>
        <option value="embedded_hdoc" <?php selected($mode_value, 'embedded_hdoc'); ?>>Embedded HDOC</option>
        <option value="embedded_hdoc_forced" <?php selected($mode_value, 'embedded_hdoc_forced'); ?>>Embedded HDOC (forced)</option>
        <option value="hdoc_in_reader" <?php selected($mode_value, 'hdoc_in_reader'); ?>>HDOC inside the Reader</option>
        <option value="standalone_hdoc" <?php selected($mode_value, 'standalone_hdoc'); ?>>Standalone HDOC</option>
    </select>
    <br><br>
    <label for="hdoc_author_name_display"><strong>Author's name:</strong></label><br>
    <select name="hdoc_author_name_display" id="hdoc_author_name_display" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($author_display, 'default'); ?>>Default (use global setting)</option>
        <option value="show"    <?php selected($author_display, 'show'); ?>>Show</option>
        <option value="hide"    <?php selected($author_display, 'hide'); ?>>Hide</option>
    </select>
    <br><br>
    <label for="hdoc_publish_date_display"><strong>Publish date:</strong></label><br>
    <select name="hdoc_publish_date_display" id="hdoc_publish_date_display" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($date_display, 'default'); ?>>Default (use global setting)</option>
        <option value="show"    <?php selected($date_display, 'show'); ?>>Show</option>
        <option value="hide"    <?php selected($date_display, 'hide'); ?>>Hide</option>
    </select>
    <br><br>
    <label for="static_web_connections_info"><strong>Connections Info:</strong></label>
    <textarea name="static_web_connections_info" id="static_web_connections_info" rows="3" style="width:100%;"><?php echo esc_textarea($connections_info); ?></textarea>
    <p class="description">Add connections info</p>
    <?php
}

function stwbpb_custom_post_endpoints_save_meta_box($post_id) {
    if (!isset($_POST['custom_post_endpoints_nonce'])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST['custom_post_endpoints_nonce']));

    if (!wp_verify_nonce($nonce, 'custom_post_endpoints_meta_box_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $allowed_modes = array('default', 'embedded_hdoc', 'embedded_hdoc_forced', 'hdoc_in_reader', 'standalone_hdoc');
    $mode = isset($_POST['hdoc_display_mode']) && in_array($_POST['hdoc_display_mode'], $allowed_modes, true)
        ? sanitize_text_field(wp_unslash($_POST['hdoc_display_mode']))
        : 'default';
    update_post_meta($post_id, '_hdoc_display_mode', $mode);

    $allowed_vis = array('default', 'show', 'hide');
    $author_vis = isset($_POST['hdoc_author_name_display']) && in_array($_POST['hdoc_author_name_display'], $allowed_vis, true)
        ? sanitize_text_field(wp_unslash($_POST['hdoc_author_name_display']))
        : 'default';
    update_post_meta($post_id, '_hdoc_author_name_display', $author_vis);

    $date_vis = isset($_POST['hdoc_publish_date_display']) && in_array($_POST['hdoc_publish_date_display'], $allowed_vis, true)
        ? sanitize_text_field(wp_unslash($_POST['hdoc_publish_date_display']))
        : 'default';
    update_post_meta($post_id, '_hdoc_publish_date_display', $date_vis);

    $allowed_tags = array(
        'doc'  => array('url' => true, 'title' => true, 'hash' => true), 
    );

    if (isset($_POST['static_web_connections_info'])) {

        $connections_info = wp_kses(wp_unslash($_POST['static_web_connections_info']), $allowed_tags);

        update_post_meta($post_id, '_static_web_connections_info', $connections_info);
    }

}
add_action('save_post', 'stwbpb_custom_post_endpoints_save_meta_box');



function stwbpb_get_effective_display_mode($post) {
    $meta_mode = get_post_meta($post->ID, '_hdoc_display_mode', true);
    if (!empty($meta_mode) && $meta_mode !== 'default') {
        return $meta_mode;
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_mode' : 'post_mode';
    return isset($settings[$key]) ? $settings[$key] : 'embedded_hdoc';
}

function stwbpb_get_effective_author_display($post) {
    $meta = get_post_meta($post->ID, '_hdoc_author_name_display', true);
    if (!empty($meta) && $meta !== 'default') {
        return $meta;
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_author_name' : 'post_author_name';
    return isset($settings[$key]) ? $settings[$key] : 'show';
}

function stwbpb_get_effective_date_display($post) {
    $meta = get_post_meta($post->ID, '_hdoc_publish_date_display', true);
    if (!empty($meta) && $meta !== 'default') {
        return $meta;
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_publish_date' : 'post_publish_date';
    return isset($settings[$key]) ? $settings[$key] : 'show';
}


?>