<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



function stwbplgn_custom_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    ?>
    <li class="comment-item" id="comment-<?php comment_ID(); ?>">
        <div class="comment-top-row">
            <div class="comment-avatar">
                <?php echo get_avatar($comment, 40); ?>
            </div>
            <div class="comment-info-column">
                <strong class="comment-author"><?php comment_author_link(); ?></strong>
                <span class="comment-date"><?php echo esc_attr(get_comment_date()); ?></span>
            </div>
        </div>
        <div class="comment-content">
            <?php comment_text(); ?>
            <div class="comment-reply">
                <?php 
                $reply_link = get_comment_reply_link(array_merge($args, array(
                    'depth'  => $depth,
                    'max_depth' => $args['max_depth']
                )));

                if ($reply_link) {
                    // Extract the href attribute using a regex
                    if (preg_match('/href=["\'](.*?)["\']/', $reply_link, $matches)) {
                        $href = $matches[1];

                        // If the href is relative, make it absolute
                        if (!preg_match('/^https?:\/\//', $href)) {
                            // Remove "/comments" if it appears at the start
                            $clean_href = preg_replace('/^\/sw-comments/', '', $href);
                            $absolute_href = home_url($clean_href);
                            $reply_link = str_replace($href, $absolute_href, $reply_link);
                        }
                    }
                }

                echo wp_kses_post($reply_link);
                ?>
            </div>
        </div>
    </li>
    <?php
}

function stwbplgn_send_comments_from_post( $post ) {

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
<?php wp_head(); ?>

</head>
<body>
<h1>Comments</h1>
<div class="comment-section">

    <?php if ($comments) : ?>
        <ul class="comment-list">
            <?php
            wp_list_comments(array(
                'style'       => 'ul',
                'short_ping'  => true,
                'avatar_size' => 32,
                'callback'    => 'stwbplgn_custom_comment_callback',
                'max_depth'   => 5,
                'reverse_top_level' => true, 
            ), $comments);
            ?>
        </ul>
    <?php else : ?>
        <p class="no-comments">No comments yet. Be the first to comment!</p>
        <a href="<?php echo esc_url(get_permalink($post->ID)) . '#respond'; ?>" class="go-to-comments">
            Reply
        </a>

    <?php endif; ?>
</div>
</body>
</html>


<?php

// Get the buffered content
$html_output = ob_get_clean();

error_log($html_output);

header('Content-Type: text/html');
$allowed_html = array(
    'html' => array(),
    'head' => array(),
    'meta' => array('charset' => array(),'name' => array(), 'content' => array()),
    'title' => array(),
    'body' => array(),
    'div' => array('class' => array(), 'id' => array()),
    'h1' => array('class' => array(), 'id' => array()),
    'ul' => array('class' => array(), 'id' => array()),
    'li' => array('class' => array(), 'id' => array()),
    'img' => array('class' => array(), 'id' => array(), 'src' => array(), 'alt' => array(), 'srcset' => array(), 'height' => array(), 'width' => array(), 'loading' => array(), 'decoding' => array()),

    'span' => array('class' => array(), 'id' => array()),
    'a' => array('href' => array(), 'title' => array(), 'rel' => array()),
    'p' => array('class' => array(), 'id' => array()),
    'i' => array(),
    'b' => array(),
    'strong' => array('class' => array(), 'id' => array()),
    'em' => array(),
    'link' => array('rel' => array(), 'href' => array(), 'type' => array()),
    'style' => array(),
    'script' => array('src' => array(), 'type' => array()),
);

echo wp_kses($html_output, $allowed_html);
}


?>