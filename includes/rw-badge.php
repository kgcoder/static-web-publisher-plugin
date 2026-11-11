<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// "This page is part of Reader's Web" badge, appended to the bottom of the
// content in embedded_hdoc_forced mode when the show_rw_badge setting is on.
// The extension's store URL is deliberately NOT in the page markup (so search
// engines don't see it); rw-badge.js creates the link only when the popup opens.

function stwbpb_should_show_rw_badge($post, $settings) {
    if (empty($settings['show_rw_badge'])) return false;
    if (!$post) return false;
    if (stwbpb_get_effective_doc_type($post) !== 'HDOC') return false;
    return stwbpb_get_doc_effective_display_mode($post) === 'embedded_hdoc_forced';
}

function stwbpb_get_rw_badge_html($settings) {
    $text = stwbpb_get_text($settings, 'rw_badge_text');
    $popup_text = stwbpb_get_text($settings, 'rw_badge_popup_text');

    // Kept in sync manually with extensionLogo in reader/Icons.js (clipPath id
    // renamed so it can't collide with anything on the theme's page).
    $icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
        . '<g clip-path="url(#stwbpb_rw_badge_clip)">'
        . '<rect x="4.3125" y="4.3125" width="15.375" height="15.375" rx="2.1875" fill="white" stroke="white" stroke-width="0.625"/>'
        . '<rect x="6" y="6.25" width="3.75" height="5.75" rx="0.625" fill="#EB3232"/>'
        . '<rect x="14" y="12.25" width="3.75" height="5.75" rx="0.625" fill="#EB3232"/>'
        . '<line x1="9.88502" y1="9.26374" x2="14.135" y2="15.2637" stroke="#EB3232" stroke-width="1.25"/>'
        . '<circle cx="14.125" cy="14.875" r="0.875" fill="#EB3232"/>'
        . '<circle cx="9.625" cy="9.25" r="0.875" fill="#EB3232"/>'
        . '</g>'
        . '<defs><clipPath id="stwbpb_rw_badge_clip"><rect width="16" height="16" fill="white" transform="translate(4 4)"/></clipPath></defs>'
        . '</svg>';

    return '<p class="hdoc-remove stwbpb-rw-badge">'
        . '<a href="#" role="button" class="stwbpb-rw-badge-link" data-popup-text="' . esc_attr($popup_text) . '">'
        . '<span class="stwbpb-rw-badge-icon">' . $icon . '</span>'
        . '<span class="stwbpb-rw-badge-text">' . esc_html($text) . '</span>'
        . '</a>'
        . '</p>';
}

add_action('wp_enqueue_scripts', function () {
    if (empty($GLOBALS['stwbpb_core_template_loaded'])) return;
    if (is_admin() || !is_singular(['post', 'page'])) return;
    global $post;
    if (!$post || !stwbpb_request_matches_post($post)) return;
    $settings = get_option('stwbpb_settings', array());
    if (!is_array($settings) || !stwbpb_should_show_rw_badge($post, $settings)) return;

    $dir_url  = plugin_dir_url(__FILE__);
    $dir_path = plugin_dir_path(__FILE__);
    wp_enqueue_style('stwbpb-rw-badge', $dir_url . 'rw-badge.css', [], filemtime($dir_path . 'rw-badge.css'));
    wp_enqueue_script('stwbpb-rw-badge', $dir_url . 'rw-badge.js', [], filemtime($dir_path . 'rw-badge.js'), true);
});
