<?php

if (!defined('ABSPATH')) {
    exit;
}


function stwbpb_proxy_fetch() {
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public proxy; auth is via allowlist check.
    $source_url = isset($_GET['source_url']) ? esc_url_raw(wp_unslash($_GET['source_url'])) : '';
    $target_url = isset($_GET['target_url']) ? esc_url_raw(wp_unslash($_GET['target_url'])) : '';
    // phpcs:enable

    if (!stwbpb_is_http_url($source_url) || !stwbpb_is_http_url($target_url)) {
        status_header(400);
        exit;
    }

    $post_id = url_to_postid($source_url);
    if (!$post_id) {
        status_header(403);
        exit;
    }

    $allowed_urls = stwbpb_get_allowed_proxy_urls($post_id);
    $target_base  = strtok($target_url, '#');

    if (!in_array($target_base, $allowed_urls, true)) {
        status_header(403);
        exit;
    }

    $response = wp_remote_get($target_url, array('timeout' => 15));
    if (is_wp_error($response)) {
        status_header(502);
        exit;
    }

    $content_type = wp_remote_retrieve_header($response, 'content-type');
    if ($content_type) {
        header('Content-Type: ' . $content_type);
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Proxying raw remote content intentionally.
    echo wp_remote_retrieve_body($response);
    exit;
}


function stwbpb_get_allowed_proxy_urls($post_id) {
    $cache_key = 'swp_connections_' . $post_id;
    $cached    = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $connections_xml = get_post_meta($post_id, '_static_web_connections_info', true);
    $urls            = stwbpb_parse_connection_urls($connections_xml);

    set_transient($cache_key, $urls, 300);
    return $urls;
}


function stwbpb_parse_connection_urls($connections_xml) {
    $urls = array();
    if (empty($connections_xml)) {
        return $urls;
    }

    $xml = @simplexml_load_string('<root>' . $connections_xml . '</root>');
    if (!$xml) {
        return $urls;
    }

    foreach ($xml->doc as $doc) {
        $url = (string) $doc['url'];
        if ($url) {
            $urls[] = strtok($url, '#');
        }
    }

    return $urls;
}


function stwbpb_is_http_url($url) {
    $scheme = wp_parse_url($url, PHP_URL_SCHEME);
    return in_array($scheme, array('http', 'https'), true);
}
