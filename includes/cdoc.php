<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_build_cdoc_source($post) {
    $title            = $post->post_title;
    $svg              = get_post_meta($post->ID, '_cdoc_svg', true);
    $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);

    $connections_allowed_tags = array(
        'connections' => array(),
        'doc'         => array('url' => true, 'title' => true, 'hash' => true),
    );

    $connectionsSection = '';
    if (!empty($connections_info)) {
        $connectionsSection = '<connections>' . PHP_EOL . $connections_info . PHP_EOL . '</connections>';
    }

    $out  = '<cdoc>' . PHP_EOL . PHP_EOL;
    $out .= '<metadata>' . PHP_EOL;
    $out .= '<title>' . esc_html(stwbpb_decode_entities($title)) . '</title>' . PHP_EOL;
    $rep_policy = stwbpb_get_effective_republishing_policy($post);
    if ($rep_policy === 'explicit_allow') {
        $out .= '<republishing-policy>allow</republishing-policy>' . PHP_EOL;
    } elseif ($rep_policy === 'prohibit') {
        $out .= '<republishing-policy>do-not-republish</republishing-policy>' . PHP_EOL;
    }
    $out .= '</metadata>' . PHP_EOL . PHP_EOL;
    if (!empty($svg)) {
        $out .= $svg . PHP_EOL . PHP_EOL;
    }
    if (!empty($connectionsSection)) {
        $out .= wp_kses($connectionsSection, $connections_allowed_tags) . PHP_EOL . PHP_EOL;
    }
    $out .= '</cdoc>';

    return $out;
}

function stwbpb_send_cdoc_for_post($post) {
    if ($post) {
        header('Content-Type: text/plain');
        echo stwbpb_build_cdoc_source($post); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already sanitized in build function
    } else {
        status_header(404);
        echo 'Page not found';
    }
}
