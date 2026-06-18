<?php

if (!defined('ABSPATH')) {
    exit;
}

function stwbpb_handle_comment_form() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below in POST branch.
    $post_id   = isset($_GET['post'])      ? absint(wp_unslash($_GET['post']))      : 0;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below in POST branch.
    $parent_id = isset($_GET['parent_id']) ? absint(wp_unslash($_GET['parent_id'])) : 0;

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        stwbpb_process_comment_form($post_id, $parent_id);
    } else {
        stwbpb_render_comment_form($post_id, $parent_id, '', '');
    }
}

function stwbpb_render_comment_form($post_id, $parent_id, $error, $submitted) {
    $current_user  = wp_get_current_user();
    $default_name  = $current_user->exists() ? $current_user->display_name : ($submitted['name']  ?? '');
    $default_email = $current_user->exists() ? $current_user->user_email   : ($submitted['email'] ?? '');
    $default_body  = $submitted['comment'] ?? '';

    $nonce = wp_create_nonce('swp_comment_form');

    $post           = $post_id ? get_post($post_id) : null;
    $post_title     = $post ? get_the_title($post) : '';
    $parent_comment = ($parent_id > 0) ? get_comment($parent_id) : null;

    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php esc_html_e('Leave a comment', 'static-web-publisher'); ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    background: #f9f9f9;
    color: #222;
    padding: 20px;
}
h2 { font-size: 16px; margin-bottom: 16px; font-weight: 600; }
.swp-cf-context {
    background: #f0f0f0;
    border-left: 3px solid #aaa;
    border-radius: 4px;
    padding: 10px 12px;
    margin-bottom: 16px;
    font-size: 13px;
}
.swp-cf-context-label { font-weight: 600; margin-bottom: 4px; }
.swp-cf-context-excerpt { color: #555; font-style: italic; line-height: 1.4; }
.swp-cf-field { margin-bottom: 12px; }
label { display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px; }
input[type="text"], input[type="email"], textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
    background: #fff;
    color: #222;
}
textarea { resize: vertical; min-height: 100px; }
input:focus, textarea:focus { outline: none; border-color: #666; }
button[type="submit"] {
    display: inline-block;
    padding: 8px 18px;
    background: #333;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}
button[type="submit"]:hover { background: #111; }
.swp-cf-error {
    background: #fff0f0;
    border: 1px solid #f5a5a5;
    color: #a00;
    padding: 10px 12px;
    border-radius: 4px;
    margin-bottom: 14px;
    font-size: 13px;
}
@media (prefers-color-scheme: dark) {
    body { background: #1a1a1a; color: #ddd; }
    input[type="text"], input[type="email"], textarea { background: #2a2a2a; color: #ddd; border-color: #555; }
    button[type="submit"] { background: #555; }
    button[type="submit"]:hover { background: #777; }
    .swp-cf-error { background: #3a1a1a; border-color: #a55; color: #f99; }
    .swp-cf-context { background: #2a2a2a; border-color: #666; }
    .swp-cf-context-excerpt { color: #aaa; }
}
</style>
</head>
<body>
<h2><?php esc_html_e('Leave a comment', 'static-web-publisher'); ?></h2>
<?php if ($parent_comment): ?>
<div class="swp-cf-context">
    <div class="swp-cf-context-label"><?php /* translators: %s: comment author's name */ echo esc_html(sprintf(__('Replying to %s', 'static-web-publisher'), $parent_comment->comment_author)); ?></div>
    <div class="swp-cf-context-excerpt"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($parent_comment->comment_content), 30)); ?></div>
</div>
<?php elseif ($post_title): ?>
<div class="swp-cf-context">
    <div class="swp-cf-context-label"><?php /* translators: %s: post title */ echo esc_html(sprintf(__('Commenting on: %s', 'static-web-publisher'), $post_title)); ?></div>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="swp-cf-error"><?php echo esc_html($error); ?></div>
<?php endif; ?>
<form method="post" action="<?php echo esc_url(home_url("/sw-comment-form/?post={$post_id}" . ($parent_id ? "&parent_id={$parent_id}" : ''))); ?>">
    <input type="hidden" name="swp_nonce"    value="<?php echo esc_attr($nonce); ?>">
    <input type="hidden" name="swp_post_id"   value="<?php echo esc_attr($post_id); ?>">
    <input type="hidden" name="swp_parent_id" value="<?php echo esc_attr($parent_id); ?>">
    <?php if (!$current_user->exists()): ?>
    <div class="swp-cf-field">
        <label for="swp-name"><?php esc_html_e('Name', 'static-web-publisher'); ?> *</label>
        <input type="text" id="swp-name" name="swp_author" required
               value="<?php echo esc_attr($default_name); ?>">
    </div>
    <div class="swp-cf-field">
        <label for="swp-email"><?php esc_html_e('Email', 'static-web-publisher'); ?> *</label>
        <input type="email" id="swp-email" name="swp_email" required
               value="<?php echo esc_attr($default_email); ?>">
    </div>
    <?php endif; ?>
    <div class="swp-cf-field">
        <label for="swp-comment"><?php esc_html_e('Comment', 'static-web-publisher'); ?> *</label>
        <textarea id="swp-comment" name="swp_comment" required><?php echo esc_textarea($default_body); ?></textarea>
    </div>
    <button type="submit"><?php esc_html_e('Post comment', 'static-web-publisher'); ?></button>
</form>
</body>
</html>
    <?php
}

function stwbpb_process_comment_form($post_id, $parent_id) {
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified with wp_verify_nonce.
    $nonce = isset($_POST['swp_nonce']) ? wp_unslash($_POST['swp_nonce']) : '';
    if (!wp_verify_nonce($nonce, 'swp_comment_form')) {
        stwbpb_render_comment_form($post_id, $parent_id, __('Security check failed. Please try again.', 'static-web-publisher'), array());
        return;
    }

    $post_id   = isset($_POST['swp_post_id'])   ? absint(wp_unslash($_POST['swp_post_id']))   : 0;
    $parent_id = isset($_POST['swp_parent_id']) ? absint(wp_unslash($_POST['swp_parent_id'])) : 0;

    $post = $post_id ? get_post($post_id) : null;
    if (!$post || $post->comment_status !== 'open') {
        stwbpb_render_comment_form($post_id, $parent_id, __('Commenting is closed for this post.', 'static-web-publisher'), array());
        return;
    }

    if ($parent_id > 0) {
        $parent_comment = get_comment($parent_id);
        if (!$parent_comment || (int) $parent_comment->comment_post_ID !== $post_id || $parent_comment->comment_approved !== '1') {
            stwbpb_render_comment_form($post_id, $parent_id, __('Invalid parent comment.', 'static-web-publisher'), array());
            return;
        }
    }

    $current_user = wp_get_current_user();

    if ($current_user->exists()) {
        $author = $current_user->display_name;
        $email  = $current_user->user_email;
        $user_id = $current_user->ID;
    } else {
        $author  = isset($_POST['swp_author']) ? sanitize_text_field(wp_unslash($_POST['swp_author'])) : '';
        $email   = isset($_POST['swp_email'])  ? sanitize_email(wp_unslash($_POST['swp_email']))       : '';
        $user_id = 0;
    }

    $comment_body = isset($_POST['swp_comment']) ? sanitize_textarea_field(wp_unslash($_POST['swp_comment'])) : '';

    $submitted = array(
        'name'    => $author,
        'email'   => $email,
        'comment' => $comment_body,
    );

    if (empty($author)) {
        stwbpb_render_comment_form($post_id, $parent_id, __('Please enter your name.', 'static-web-publisher'), $submitted);
        return;
    }

    if (empty($email) || !is_email($email)) {
        stwbpb_render_comment_form($post_id, $parent_id, __('Please enter a valid email address.', 'static-web-publisher'), $submitted);
        return;
    }

    if (empty($comment_body)) {
        stwbpb_render_comment_form($post_id, $parent_id, __('Please enter a comment.', 'static-web-publisher'), $submitted);
        return;
    }

    $commentdata = array(
        'comment_post_ID'      => $post_id,
        'comment_author'       => $author,
        'comment_author_email' => $email,
        'comment_author_url'   => '',
        'comment_content'      => $comment_body,
        'comment_type'         => 'comment',
        'comment_parent'       => $parent_id,
        'user_id'              => $user_id,
    );

    $comment_id = wp_insert_comment(wp_filter_comment($commentdata));

    if (!$comment_id) {
        stwbpb_render_comment_form($post_id, $parent_id, __('Could not save your comment. Please try again.', 'static-web-publisher'), $submitted);
        return;
    }

    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php esc_html_e('Comment submitted', 'static-web-publisher'); ?></title>
<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    background: #f9f9f9;
    color: #222;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 80vh;
}
.swp-cf-success {
    text-align: center;
    max-width: 320px;
}
.swp-cf-success p { margin-bottom: 8px; line-height: 1.5; }
.swp-cf-success .swp-check { font-size: 40px; margin-bottom: 12px; }
@media (prefers-color-scheme: dark) {
    body { background: #1a1a1a; color: #ddd; }
}
</style>
</head>
<body>
<div class="swp-cf-success">
    <div class="swp-check">&#10003;</div>
    <p><?php esc_html_e('Thank you for your comment!', 'static-web-publisher'); ?></p>
    <p><?php esc_html_e('It will appear once approved.', 'static-web-publisher'); ?></p>
</div>
<script>window.parent.postMessage({type:'swp-comment-submitted'}, '*');</script>
</body>
</html>
    <?php
}
