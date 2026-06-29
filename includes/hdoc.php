<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_strip_wp_tags($content) {
    // Define the regular expression pattern to match WordPress-specific tags
    $pattern = '/<!--\s*\/?wp:.*?-->/i';
    // Remove the tags from the content
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<\/?figure.*?>/i';

    $content = preg_replace($pattern, '', $content);


    return $content;
}


function stwbpb_send_hdoc_for_post($post){
    if ($post) {
        
        $settings = get_option('stwbpb_settings', array()); // Ensure a default empty array

        $permalink = get_permalink($post->ID);

        
        $title = $post->post_title;
        $htmlContent = $post->post_content;

        $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);


   
        $embedPattern = '/<!-- wp:embed \{"url":"https:\/\/www\.youtube\.com\/(watch\?v=|embed\/)([^"]+)",.*\} -->.*<div class="wp-block-embed__wrapper">\s*(https:\/\/www\.youtube\.com\/(watch\?v=|embed\/)[^<]+)\s*<\/div>.*<!-- \/wp:embed -->/sU';
        
        $callback = function ($matches) {
            $youtubeId = $matches[2];
            $width = 560;
            $height = 315;
            return "<iframe width=\"$width\" height=\"$height\" src=\"https://www.youtube.com/embed/$youtubeId\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe>";
        };

        $htmlContent = preg_replace_callback($embedPattern, $callback, $htmlContent);

        $htmlContent = stwbpb_strip_wp_tags($htmlContent);


        //$finalContent = '<h1>' . $title . "</h1>" .  $htmlContent;

        $panels_escaped = stwbpb_get_panels($post);

        $connectionsSection = '';
        if(!empty($connections_info)){
            $connectionsSection = '<connections>' . PHP_EOL . $connections_info . PHP_EOL . '</connections>';

        }

        $allowed_tags = wp_kses_allowed_html('post'); // Get default allowed tags
        $allowed_tags['iframe'] = array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'referrerpolicy'  => true,
            'sandbox'         => true
        );

        $panels_allowed_tags = array(
            'panels' => true,
            'top' => true,
            'post-nav' => array(),
            'prev' => array('href' => true),
            'next' => array('href' => true),
            'side' => array('side' => true),
            'bottom' => true,
            'comments' => array('title' => true, 'empty' => true, 'leave-comment-url' => true, 'reply-label' => true, 'leave-comment-label' => true),
            'logo' => array('src' => true, 'href' => true),
            'site-name' => array('href' => true),
            'a' => array('href' => true),
            'section' => array('title' => true),
            'bottom-message' => array(),
        );


        $connections_allowed_tags = array(
            'connections' =>  array(),
            'doc'  => array('url' => true, 'title' => true, 'hash' => true), 
        );


        header('Content-Type: text/plain');
        echo '<hdoc>' . PHP_EOL  . PHP_EOL;
        echo '<metadata>' . PHP_EOL;
        echo '<title>' . esc_html( $title ) . '</title>' . PHP_EOL;
        echo '</metadata>' . PHP_EOL . PHP_EOL;

        echo wp_kses($panels_escaped,$panels_allowed_tags) . PHP_EOL . PHP_EOL;

        echo '<header>' . PHP_EOL;
        echo '<h1>' . esc_html($title) . '</h1>' . PHP_EOL;
        if (stwbpb_get_effective_author_display($post) === 'show') {
            $author_id   = get_post_field('post_author', $post->ID);
            $author_name = get_the_author_meta('display_name', $author_id);
            echo '<author>' . esc_html($author_name) . '</author>' . PHP_EOL;
        }
        if (stwbpb_get_effective_date_display($post) === 'show') {
            $date = get_the_date(get_option('date_format'), $post->ID);
            echo '<date>' . esc_html($date) . '</date>' . PHP_EOL;
        }
        echo '</header>' . PHP_EOL . PHP_EOL;

        echo '<content>' . wp_kses($htmlContent,$allowed_tags) . '</content>' . PHP_EOL . PHP_EOL; 
            
        echo wp_kses($connectionsSection, $connections_allowed_tags) . PHP_EOL . PHP_EOL;
        echo '</hdoc>';
    } else {
        // Handle post not found
        status_header(404);
        echo 'Page not found';
    }
}







