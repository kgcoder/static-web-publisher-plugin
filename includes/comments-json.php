<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function stwbpb_send_comments_json_from_post() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: only reading sanitized GET params.
    $post_id = isset($_GET['post']) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
    if (!$post_id) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    $post = get_post($post_id);
    if (empty($post)) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    // Pagination parameters: page and per_page
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: only reading sanitized GET params.
    $page = isset($_GET['page']) ? max(1, absint( wp_unslash( $_GET['page'] ) )) : 1;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: only reading sanitized GET params.
    $per_page = isset($_GET['per_page']) ? absint( wp_unslash( $_GET['per_page'] ) ) : 10;
    if ($per_page < 1) {
        $per_page = 10;
    } elseif ($per_page > 100) {
        $per_page = 100; // Prevent excessively large requests
    }

    $offset = ($page - 1) * $per_page;

    // Order parameter: default to DESC, allow ASC via GET ?order=asc
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: only reading sanitized GET params.
    $order_param = isset($_GET['order']) ? strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : '';
    $order = ($order_param === 'asc') ? 'ASC' : 'DESC';

    $commenting_open = $post->comment_status === 'open';

    $comments = get_comments(array(
        'post_id' => $post_id,
        'status' => 'approve',
        'number' => $per_page,
        'offset' => $offset,
        'orderby' => 'comment_date_gmt',
        'order' => $order,

    ));

    $data = array_map(function($comment) use ($post_id, $commenting_open) {
        $comment_type = empty($comment->comment_type) ? 'comment' : $comment->comment_type;

        // Dates
        $date_local = get_comment_date('c', $comment);
        $date_gmt = get_comment_time('c', true, false, $comment);

        // Content rendered similar to REST API
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter name.
        $content_rendered = apply_filters('comment_text', $comment->comment_content, $comment);

        // Author avatar urls like REST: 24, 48, 96
        $avatar_24 = get_avatar_url($comment, array('size' => 24));
        $avatar_48 = get_avatar_url($comment, array('size' => 48));
        $avatar_96 = get_avatar_url($comment, array('size' => 96));

        $item = array(
            'id' => (int) $comment->comment_ID,
            'post' => (int) $post_id,
            'parent' => (int) $comment->comment_parent,
            'author' => (int) $comment->user_id,
            'author_name' => (string) $comment->comment_author,
            'author_url' => (string) $comment->comment_author_url,
            'author_avatar_urls' => array(
                '24' => $avatar_24,
                '48' => $avatar_48,
                '96' => $avatar_96,
            ),
            'date' => $date_local,
            'date_gmt' => $date_gmt,
            'content' => array(
                'rendered' => $content_rendered,
            ),
            'link' => get_comment_link($comment),
            'type' => $comment_type,
        );

        if ($commenting_open) {
            $item['reply-url'] = home_url("/sw-comment-form/?post={$post_id}&parent_id={$comment->comment_ID}");
        }

        return $item;
    }, $comments);

    wp_send_json($data);
}