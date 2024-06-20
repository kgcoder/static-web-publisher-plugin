<?php

/*
Plugin Name: Second Web Plugin
Description: Publishes your posts and pages on the Second Web
Version: 1.0
Author: Karen Grigorian
Author URI: https://github.com/kgcoder
*/


if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

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

            $pattern = '/<!-- wp:embed \{"url":"(https:\/\/www\.youtube\.com\/watch\?v=[^"]+)",.*"className":"wp-embed-aspect-(\d+)-(\d+) wp-has-aspect-ratio"\} -->.*<div class="wp-block-embed__wrapper">\s*(https:\/\/www\.youtube\.com\/watch\?v=[^<]+)\s*<\/div>.*<!-- \/wp:embed -->/sU';
            
            $callback = function ($matches) {
                $url = $matches[1];
                $width = $matches[2];
                $height = $matches[3];
                return "<div class=\"youtube_video\" w=\"$width\" h=\"$height\">$url</div>";
            };

            $htmlContent = preg_replace_callback($pattern, $callback, $htmlContent);

            $htmlContent = strip_wp_tags($htmlContent);
            
            //$allowed_tags = ['p', 'a', 'strong', 'h1', 'h2', 'h3', 'h4', 'img', 'figure'];
            
           // $htmlContent = strip_unwanted_tags($htmlContent,$allowed_tags);
            
            // $htmlContent = preg_replace('/<img[^>]*>/', "<br>$0<br>", $htmlContent);



            // $converter = new HtmlConverter();


            // $markdownContent = $converter->convert($htmlContent);

//wp_strip_all_tags
            $finalContent = '<h1>' . $title . "</h1>" . $htmlContent . "<p>---</p><p><a href=\"" . $permalink . "\">" . "Original page</a></p>";


            // Output the simplified content
            header('Content-Type: text/plain');
            echo '<sw-text><body>' . $finalContent . '</body></sw-text>';
        } else {
            // Handle post not found
            status_header(404);
            echo 'Post not found';
        }
        exit;
    }
    if (isset($wp_query->query_vars['comments_custom_post_slug'])) {
        $slug = $wp_query->query_vars['comments_custom_post_slug'];

        // Fetch the post by slug
        $post = get_page_by_path($slug, OBJECT, 'post');

        if ($post) {
            header('Content-Type: text/plain');
            echo "finalContent";
        
        }else{
            status_header(404);
            echo 'Post not found';
        }
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

        $simplified_link = sprintf('<p><a href="%s">[SW]</a></p>',$link);

        // Get the position setting (assuming you have a setting for this)
        $position = get_option( 'custom_post_endpoints_button_position', 'bottom' );

        if ( 'top' === $position ) {
            $content = $simplified_link . $content;
        } elseif ( 'bottom' === $position ) {
            $content .= $simplified_link;
        }
    }

    return $content;
}
add_filter( 'the_content', 'custom_post_endpoints_add_link_to_content' );