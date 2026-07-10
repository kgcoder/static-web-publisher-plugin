<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_build_condoc_source($post) {
    $title            = $post->post_title;
    $description      = get_post_meta($post->ID, '_condoc_description', true);
    $main_url         = get_post_meta($post->ID, '_condoc_main_url', true);
    $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);

    $connections_allowed_tags = array(
        'connections' => array(),
        'doc'         => array('url' => true, 'title' => true, 'hash' => true),
    );

    $connectionsSection = '';
    if (!empty($connections_info)) {
        $connectionsSection = '<connections>' . PHP_EOL . $connections_info . PHP_EOL . '</connections>';
    }

    $out  = '<condoc>' . PHP_EOL . PHP_EOL;
    $out .= '<title>' . esc_html(stwbpb_decode_entities($title)) . '</title>' . PHP_EOL . PHP_EOL;
    if (!empty($description)) {
        $out .= '<description>' . esc_html(stwbpb_decode_entities($description)) . '</description>' . PHP_EOL . PHP_EOL;
    }
    if (!empty($main_url)) {
        $out .= '<main>' . htmlspecialchars($main_url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</main>' . PHP_EOL . PHP_EOL;
    }
    if (!empty($connectionsSection)) {
        $out .= wp_kses($connectionsSection, $connections_allowed_tags) . PHP_EOL . PHP_EOL;
    }
    $out .= '</condoc>';

    return $out;
}

function stwbpb_send_condoc_for_post($post) {
    if ($post) {
        header('Content-Type: text/plain');
        echo stwbpb_build_condoc_source($post); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already sanitized in build function
    } else {
        status_header(404);
        echo 'Page not found';
    }
}
