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
       
        if (!isset($settings['serve_hdoc_from_different_url']) || get_post_meta($post->ID, '_disable_static_web_link', true) === '1') {
            status_header(404);
            echo 'Page not found';
            return;
        }


        
        
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


        $modify_internal_links = isset($settings['modify_internal_links']) ? $settings['modify_internal_links'] : '';


        if(!empty($modify_internal_links)){
            $htmlContent = stwbpb_modify_internal_links_in_html($htmlContent);
        }

        $modify_external_links = isset($settings['modify_external_links']) ? $settings['modify_external_links'] : '';

        $display_author_name = isset($settings['display_author_name']) ? $settings['display_author_name'] : '';
        $display_publish_date = isset($settings['display_publish_date']) ? $settings['display_publish_date'] : '';




        if(!empty($modify_external_links)){
            $htmlContent = stwbpb_modify_external_links_in_html($htmlContent);
        }
  
        $originalPageDisabled = get_post_meta($post->ID, '_disable_original_page', true) === '1';

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
            'side' => array('side' => true),
            'bottom' => true,
            'comments' => array('title' => true, 'empty' => true),
            'logo' => array('src' => true, 'href' => true, 'static' => true),
            'site-name' => array('href' => true, 'static' => true),
            'a' => array('href' => true, 'static' => true),
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
        if(!$originalPageDisabled){
            echo '<link rel="alternate" type="text/html" title="' . esc_attr( $title ) . '" href="' . esc_url( $permalink ) . '" />' . PHP_EOL;
        }
        echo '</metadata>' . PHP_EOL . PHP_EOL;
        
        echo '<header>' . PHP_EOL;
        echo '<h1>' . esc_html($title) . '</h1>' . PHP_EOL;
        if(!empty($display_author_name)){
            $author_id   = get_post_field('post_author', $post->ID);
            $author_name = get_the_author_meta('display_name', $author_id);
            echo '<author>' . esc_html($author_name) . '</author>' . PHP_EOL;
        }
        if(!empty($display_publish_date)){
            $date = get_the_date(get_option('date_format'), $post->ID);
            echo '<date>' . esc_html($date) . '</date>' . PHP_EOL;
        }
        echo '</header>' . PHP_EOL . PHP_EOL;

        echo '<content>' . wp_kses($htmlContent,$allowed_tags) . '</content>' . PHP_EOL . PHP_EOL; 
       
        echo wp_kses($panels_escaped,$panels_allowed_tags) . PHP_EOL . PHP_EOL;
        echo wp_kses($connectionsSection, $connections_allowed_tags) . PHP_EOL . PHP_EOL;
        echo '</hdoc>';
    } else {
        // Handle post not found
        status_header(404);
        echo 'Page not found';
    }
}


function stwbpb_modify_internal_links_in_html($htmlContent) {
    // Get the site's base URL
    $site_url = home_url();

    // Parse the site's URL to extract the domain
    $parsed_url = wp_parse_url($site_url);
    $site_domain = $parsed_url['host']; // e.g., 'mywebsite.com'

    // Regex pattern to find all <a> tags with href attributes
    $pattern = '/<a\s+[^>]*href=["\'](.*?)["\']/i';

    // Callback function to modify URLs
    $modifiedHtml = preg_replace_callback($pattern, function ($matches) use ($site_domain) {
        $original_url = $matches[1];

        // Check if the URL belongs to the site's domain
        if (strpos($original_url, 'http://' . $site_domain) === 0 || strpos($original_url, 'https://' . $site_domain) === 0) {
            // Replace http/https with sw/sws
            $protocol_replacement = strpos($original_url, 'https://') === 0 ? 'sws://' : 'sw://';

            // Remove the protocol and add the new one
            $url_without_protocol = preg_replace('/^https?:\/\//', '', $original_url);

            // Add '/sw/' after the domain name
            $modified_url = preg_replace("/^{$site_domain}/", "{$site_domain}/sw", $url_without_protocol);

            // Combine the new protocol with the modified URL
            $modified_url = $protocol_replacement . $modified_url;

            // Replace the original URL in the anchor tag
            return str_replace($original_url, $modified_url, $matches[0]);
        }

        // Return the original match if not internal
        return $matches[0];
    }, $htmlContent);

    return $modifiedHtml;
}


function stwbpb_modify_external_links_in_html($htmlContent) {
    // Regex pattern to find <a> tags with a data-sw attribute
    $pattern = '/<a\s+[^>]*href=["\'](.*?)["\'][^>]*data-sw=["\'](.*?)["\'][^>]*>/i';

    // Callback function to replace href and remove data-sw
    $modifiedHtml = preg_replace_callback($pattern, function ($matches) {
        $original_href = $matches[1]; // Original href value
        $data_sw_value = $matches[2]; // Value from data-sw attribute

        // Replace href with data-sw value and remove the data-sw attribute
        $modified_tag = preg_replace(
            array('/href=["\'].*?["\']/', '/\s+data-sw=["\'].*?["\']/'), // Patterns to replace
            array("href=\"$data_sw_value\"", ''), // Replacements
            $matches[0]
        );

        return $modified_tag;
    }, $htmlContent);

    return $modifiedHtml;
}



