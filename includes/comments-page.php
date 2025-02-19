<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function stwebplgn_custom_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    ?>
    <li class="comment-item" id="comment-<?php comment_ID(); ?>">
        <div class="comment-top-row">
            <div class="comment-avatar">
                <?php echo get_avatar($comment, 40); ?>
            </div>
            <div class="comment-info-column">
                <strong class="comment-author"><?php comment_author_link(); ?></strong>
                <span class="comment-date"><?php echo get_comment_date(); ?></span>
            </div>
        </div>
        <div class="comment-content">
            <?php comment_text(); ?>
            <div class="comment-reply">
                <?php 
                comment_reply_link(array_merge($args, array(
                    'depth'  => $depth,
                    'max_depth' => $args['max_depth']
                ))); 
                ?>
            </div>
        </div>
    </li>
    <?php
}

function stwebplgn_send_comments_from_post( $post ) {

    if (empty($post)) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    $post_id = $post->ID;

    $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));


    // Start output buffering
    ob_start();

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comments for <?php echo esc_html($post->post_title); ?></title>
<link rel="stylesheet" href="<?php echo plugins_url('comments.css', __FILE__); ?>">
</head>
<body>
<div class="comment-section">
    <h1>Comments</h1>

    <?php if ($comments) : ?>
        <ul class="comment-list">
            <?php
            wp_list_comments(array(
                'style'       => 'ul',
                'short_ping'  => true,
                'avatar_size' => 32,
                'callback'    => 'stwebplgn_custom_comment_callback',
                'max_depth'   => 5,
            ), $comments);
            ?>
        </ul>
    <?php else : ?>
        <p class="no-comments">No comments yet. Be the first to comment!</p>
        <a href="<?php echo get_permalink($post->ID) . '#respond'; ?>" class="go-to-comments">
            Reply
        </a>

    <?php endif; ?>
</div>
</body>
</html>


<?php

// Get the buffered content
$html_output = ob_get_clean();
header('Content-Type: text/html');
echo $html_output; 
}


?>