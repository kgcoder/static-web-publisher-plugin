<?php

function add_sw_link_to_rss_post($content) {
    if (is_feed()) {
        global $post;

        // Get the standard permalink
        $permalink = get_permalink($post->ID);

        // Strip the home URL part
        $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);

        // Create the custom link
        $link = preg_replace('/^http/', 'sw', home_url("/sw{$path_part}"));

        // Wrap in a paragraph tag
        $content .= '<p><a href="' . esc_url($link) . '">Static version of the post</a></p>';
    }

    return $content;
}

add_filter('the_content_feed', 'add_sw_link_to_rss_post');
add_filter('the_excerpt_rss', 'add_sw_link_to_rss_post');
