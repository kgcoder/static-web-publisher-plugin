<?php

/*
Plugin Name: Static Web Plugin
Description: Publishes your posts and pages on the Static Web
Version: 1.0
Author: Karen Grigorian
Author URI: https://github.com/kgcoder
*/


// if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
//     require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
// }

// use League\HTMLToMarkdown\HtmlConverter;


//add_filter('the_content','addToEndOfPost');

// function addToEndOfPost($content){
//     // echo '<script>console.log("content: ' . $content . '")</script>';

//     return $content . 'fuck' . '<script>console.log("content: \'' . $content . '\'")</script>';

// }





// function custom_post_endpoints_register_routes() {
//     register_rest_route( 'sw/v1', '/(?P<slug>[-\w]+)', array(
//         'methods'  => 'GET',
//         'callback' => 'custom_post_endpoints_get_post_by_slug',
//     ) );
//     error_log('Custom endpoint registered: /sw/v1/(?P<slug>[-\w]+)');

// }

// add_action( 'rest_api_init', 'custom_post_endpoints_register_routes' );



// function custom_post_endpoints_get_post_by_slug( $request ) {
//     $slug = $request['slug'];
//     error_log("Received request for slug: $slug");
//     $args = array(
//         'name'        => $slug,
//         'post_type'   => 'post',
//         'post_status' => 'publish',
//         'numberposts' => 1,
//     );
//     $posts = get_posts( $args );


//     if ( empty( $posts ) ) {
//         return new WP_Error( 'post_not_found', 'Post not found', array( 'status' => 404 ) );
//     }

//     $post = $posts[0];
//     $response = array(
//         'id'      => $post->ID,
//         'title'   => $post->post_title,
//         'content' => wp_strip_all_tags( $post->post_content ),
//     );

//    // $response = $slug;

//     return rest_ensure_response( $response );
// }



function custom_post_endpoints_rewrite_rules() {
    add_rewrite_rule(
        '^sw/v1/([^/]+)/?$',
        'index.php?custom_post_slug=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^sw/v1/comments/([^/]+)/?$',
        'index.php?comments_custom_post_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'custom_post_endpoints_rewrite_rules');

function custom_post_endpoints_query_vars($query_vars) {
    $query_vars[] = 'custom_post_slug';
    $query_vars[] = 'comments_custom_post_slug';
    return $query_vars;
}
add_filter('query_vars', 'custom_post_endpoints_query_vars');

function strip_wp_tags($content) {
    // Define the regular expression pattern to match WordPress-specific tags
    $pattern = '/<!--\s*\/?wp:.*?-->/i';
    // Remove the tags from the content
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<\/?figure.*?>/i';

    $content = preg_replace($pattern, '', $content);


    return $content;
}

function strip_unwanted_tags($content, $allowed_tags = []) {
    // Create a string of allowed tags for use with wp_kses
    $allowed_html = [];
    foreach ($allowed_tags as $tag) {
        $allowed_html[$tag] = [];
    }
    // Use wp_kses to filter the content
    $content = wp_kses($content, $allowed_html);
    return $content;
}


function custom_post_endpoints_template_redirect() {
    global $wp_query;

    if (isset($wp_query->query_vars['custom_post_slug'])) {
        $slug = $wp_query->query_vars['custom_post_slug'];

        // Fetch the post by slug
        $post = get_page_by_path($slug, OBJECT, 'post');


        
        if ($post) {

            $permalink = get_permalink($post->ID);
            
            $title = $post->post_title;
            $htmlContent = $post->post_content;

            // header('Content-Type: text/plain');
            // echo  $htmlContent;

            $pattern = '/<!-- wp:embed \{"url":"https:\/\/www\.youtube\.com\/watch\?v=([^"]+)",.*"className":"wp-embed-aspect-(\d+)-(\d+) wp-has-aspect-ratio"\} -->.*<div class="wp-block-embed__wrapper">\s*(https:\/\/www\.youtube\.com\/watch\?v=[^<]+)\s*<\/div>.*<!-- \/wp:embed -->/sU';
            
            $callback = function ($matches) {
                $youtubeId = $matches[1];
                $width = 560;// $matches[2];
                $height = 315;// $matches[3];
                return "<iframe width=\"$width\" height=\"$height\" src=\"https://www.youtube.com/embed/$youtubeId\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe><h3>hello</h3>";
            };

            $htmlContent = preg_replace_callback($pattern, $callback, $htmlContent);

            $htmlContent = strip_wp_tags($htmlContent);
            
            //$allowed_tags = ['p', 'a', 'strong', 'h1', 'h2', 'h3', 'h4', 'img', 'figure'];
            
           // $htmlContent = strip_unwanted_tags($htmlContent,$allowed_tags);
            
            // $htmlContent = preg_replace('/<img[^>]*>/', "<br>$0<br>", $htmlContent);






            //$testVideo = "<p>sfsdfdsflkj</p><iframe width=\"560px\" height=\"315px\" src=\"https://www.youtube.com/embed/oVfHeWTKjag\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe><h3 class=\"p1\"><span class=\"s1\">What I did about it</span></h3>";

//wp_strip_all_tags

//http://swplugintest.local/my-test-post/
            $finalContent = '<h1>' . $title . "</h1>" . /*$testVideo .*/ $htmlContent . "<p>---</p><p><a href=\"" . $permalink . "\">" . "Original page</a></p>";

            $link = home_url( "/sw/v1/comments/{$post->post_name}");
            $head = '<head><extra><right>' . $link . '</right></extra></head>';

            // Output the simplified content
            header('Content-Type: text/plain');
            echo '<hdoc>' . $head .'<body>' . $finalContent . '</body></hdoc>';
        } else {
            // Handle post not found
            status_header(404);
            echo 'Post not found';
        }
        exit;
    }
    if (isset($wp_query->query_vars['comments_custom_post_slug'])) {
        $slug = $wp_query->query_vars['comments_custom_post_slug'];

        
        //Fetch the post by slug
        $post = get_page_by_path($slug, OBJECT, 'post');

        if (empty($post)) {
            return new WP_Error('post_not_found', 'Post not found', ['status' => 404]);
        }

        $post_id = $post->ID;

        // header('Content-Type: text/plain');
        // $hello = 'hello';
        // echo $post_id;

        $comments = get_comments(['post_id' => $post_id, 'status' => 'approve']);

        if (empty($comments)) {
            return new WP_Error('no_comments', 'No comments found for this post', ['status' => 404]);
        }


        // Start output buffering

    //     remove_all_actions('wp_footer');
    // remove_all_actions('wp_body_open');
    // remove_all_actions('the_content');
    ob_start();


    


      



    // Include the comment section template
    // echo '<!DOCTYPE html>';
    // echo '<html>';
    // echo '<body>';
    // echo '<div class="comment-section">';
    // echo '<h3>Comments</h3>';
    // wp_list_comments([
    //     'style'       => 'div',
    //     'short_ping'  => true,
    //     'avatar_size' => 50,
    // ], $comments);
    // echo '</div>';
    // echo '</body>';
    // echo '</html>';

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
    wp_list_comments([
        'style'       => 'div',
        'short_ping'  => true,
        'avatar_size' => 32,
    ], $comments);
    ?>
    </div>
    </body>
    </html>

    <?php

    // Get the buffered content
    $html_output = ob_get_clean();
    header('Content-Type: text/html');
    echo $html_output; 


     //return rest_ensure_response($html_output);

      

   

        // if ($post) {
        //     header('Content-Type: text/plain');
        //     echo $comments_data;
        
        // }else{
        //     status_header(404);
        //     echo 'Post not found';
        // }
        // $html_output = ob_get_clean();
        
        // header('Content-Type: text/html');
        //  echo $html_output;


        exit;
    

 


    }
    

}
add_action('template_redirect', 'custom_post_endpoints_template_redirect');


function custom_post_endpoints_add_link_to_content( $content ) {
    global $post;
    global $wp_query;


    if ( is_single() && $post && 'post' === $post->post_type ) {


        // if (isset($wp_query->query_vars['custom_post_slug'])) {
        //     $slug = $wp_query->query_vars['custom_post_slug'];
        $link = preg_replace('/^http/', "sw", home_url( "/sw/v1/{$post->post_name}"));

        $simplified_link = sprintf('<a href="%s">[SW]</a>',$link);

        // Get the position setting (assuming you have a setting for this)
        $position = get_option( 'custom_post_endpoints_button_position', 'bottom' );

        $info_url = "https://google.com";

        $info_link = sprintf('<a href="%s" target="_blank">Learn about Static Web</a>',$info_url);

        $links_paragraph = '<p>' . $simplified_link . ' ' . $info_link . '</p>';


        if ( 'top' === $position ) {
            $content = $links_paragraph . $content;
        } elseif ( 'bottom' === $position ) {
            $content .= $links_paragraph;
        }
    }

    return $content;
}
add_filter( 'the_content', 'custom_post_endpoints_add_link_to_content' );