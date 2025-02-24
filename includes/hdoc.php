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

        if (get_post_meta($post->ID, '_disable_static_web_link', true) === '1') {
            status_header(404);
            echo 'Page not found';
            return;
        }
        
        
        $permalink = get_permalink($post->ID);

        
        $title = $post->post_title;
        $htmlContent = $post->post_content;

        $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);


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

        $htmlContent = stwbpb_strip_wp_tags($htmlContent);



        $settings = get_option('stwbpb_settings', array()); // Ensure a default empty array
        $modify_internal_links = isset($settings['modify_internal_links']) ? $settings['modify_internal_links'] : '';


        if(!empty($modify_internal_links)){
            $htmlContent = stwbpb_modify_internal_links_in_html($htmlContent);
        }

        $modify_external_links = isset($settings['modify_external_links']) ? $settings['modify_external_links'] : '';



        if(!empty($modify_external_links)){
            $htmlContent = stwbpb_modify_external_links_in_html($htmlContent);
        }
  
        $finalContent = '<h1>' . $title . "</h1>" .  $htmlContent . "<p>---</p><p><a href=\"" . $permalink . "\">" . "Original page</a></p>";

        $panels_escaped = stwbpb_get_panels($post);

        $connectionsSection = '';
        if(!empty($connections_info)){
            $connectionsSection = '<connections>' . $connections_info . '</connections>';

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

        $connections_allowed_tags = array(
            'connections' =>  array(),
            'doc'  => array('url' => true, 'title' => true, 'hash' => true), 
        );


       
        header('Content-Type: text/plain');
        echo '<hdoc>';
        echo $panels_escaped; // phpcs:ignore WordPress.Security.EscapeOutput
        echo '<html>' . wp_kses($finalContent,$allowed_tags) . '</html>'; 
        echo wp_kses($connectionsSection, $connections_allowed_tags);
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



