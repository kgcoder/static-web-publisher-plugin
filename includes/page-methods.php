<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function stwbpb_custom_post_endpoints_add_meta_box() {
    add_meta_box(
        'custom_post_endpoints_meta_box',
        'Static Web Link Settings',
        'stwbpb_custom_post_endpoints_meta_box_callback',
        array('post', 'page'), // Enable for posts and pages
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'stwbpb_custom_post_endpoints_add_meta_box');

function stwbpb_custom_post_endpoints_meta_box_callback($post) {
    $value = get_post_meta($post->ID, '_disable_static_web_link', true);
    $disable_original_page_value = get_post_meta($post->ID, '_disable_original_page', true);

    $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);

    wp_nonce_field('custom_post_endpoints_meta_box_nonce', 'custom_post_endpoints_nonce');
    ?>
    <label for="disable_static_web_link">
        <input type="checkbox" name="disable_static_web_link" id="disable_static_web_link" value="1" <?php checked($value, '1'); ?> />
        Disable HDOC version of this post/page
    </label>
    <br><br>
    <label for="disable_original_page">
        <input type="checkbox" name="disable_original_page" id="disable_original_page" value="1" <?php checked($disable_original_page_value, '1'); ?> />
        Disable the original post/page
    </label>
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

    $value = isset($_POST['disable_static_web_link']) ? '1' : '';
    update_post_meta($post_id, '_disable_static_web_link', $value);

    $value = isset($_POST['disable_original_page']) ? '1' : '';
    update_post_meta($post_id, '_disable_original_page', $value);

    

    $allowed_tags = array(
        'doc'  => array('url' => true, 'title' => true, 'hash' => true), 
    );

    if (isset($_POST['static_web_connections_info'])) {

        $connections_info = wp_kses(wp_unslash($_POST['static_web_connections_info']), $allowed_tags);

        update_post_meta($post_id, '_static_web_connections_info', $connections_info);
    }

}
add_action('save_post', 'stwbpb_custom_post_endpoints_save_meta_box');




function stwbpb_output_alternate_hdoc_link_in_head() {
	global $post;

    $settings = get_option('stwbpb_settings', array()); // Ensure a default empty array
       
	if ((is_single() || is_page()) && $post && ('post' === $post->post_type || 'page' === $post->post_type)) {
		if (!isset($settings['serve_hdoc_from_different_url']) || get_post_meta($post->ID, '_disable_static_web_link', true) === '1') {
			return;
		}

        $rewrite_prefix = $settings['rewrite_prefix'];

		$permalink = get_permalink($post->ID);
		$path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);
		$link = home_url("/{$rewrite_prefix}{$path_part}");

		$title = get_the_title($post);

		echo '<link rel="alternate" type="application/hdoc+xml" title="' . esc_attr($title) . '" href="' . esc_url($link) . '" />' . "\n";
	}
}
add_action('wp_head', 'stwbpb_output_alternate_hdoc_link_in_head');




?>