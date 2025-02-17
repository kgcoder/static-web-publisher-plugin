<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function send_comments_from_post( $post ) {

    if (empty($post)) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    $post_id = $post->ID;

    $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));

    if (empty($comments)) {
        return new WP_Error('no_comments', 'No comments found for this post', array('status' => 404));
    }


    // Start output buffering
    ob_start();

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comments for <?php echo esc_html($post->post_title); ?></title>
    <?php
    // Include only required theme styles
    wp_enqueue_style('style', get_stylesheet_uri());
    wp_print_styles();
    ?>
</head>
<body>
<div class="comment-section">
<h3>Comments</h3>
<?php
wp_list_comments(array(
    'style'       => 'div',
    'short_ping'  => true,
    'avatar_size' => 32,
), $comments);
?>
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