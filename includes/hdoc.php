<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbplgn_strip_wp_tags($content) {
    // Define the regular expression pattern to match WordPress-specific tags
    $pattern = '/<!--\s*\/?wp:.*?-->/i';
    // Remove the tags from the content
    $content = preg_replace($pattern, '', $content);

    $pattern = '/<\/?figure.*?>/i';

    $content = preg_replace($pattern, '', $content);


    return $content;
}


function stwbplgn_send_hdoc_for_post($post){
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

        $htmlContent = stwbplgn_strip_wp_tags($htmlContent);



        $settings = get_option('stwbplgn_settings', array()); // Ensure a default empty array
        $modify_internal_links = isset($settings['modify_internal_links']) ? $settings['modify_internal_links'] : '';


        if(!empty($modify_internal_links)){
            $htmlContent = stwbplgn_modify_internal_links_in_html($htmlContent);
        }

        $modify_external_links = isset($settings['modify_external_links']) ? $settings['modify_external_links'] : '';



        if(!empty($modify_external_links)){
            $htmlContent = stwbplgn_modify_external_links_in_html($htmlContent);
        }
        
        //$allowed_tags = ['p', 'a', 'strong', 'h1', 'h2', 'h3', 'h4', 'img', 'figure'];
        
       // $htmlContent = strip_unwanted_tags($htmlContent,$allowed_tags);
        
        // $htmlContent = preg_replace('/<img[^>]*>/', "<br>$0<br>", $htmlContent);






        //$testVideo = "<p>sfsdfdsflkj</p><iframe width=\"560px\" height=\"315px\" src=\"https://www.youtube.com/embed/oVfHeWTKjag\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe><h3 class=\"p1\"><span class=\"s1\">What I did about it</span></h3>";

//wp_strip_all_tags

//http://swplugintest.local/my-test-post/
        $finalContent = '<h1>' . $title . "</h1>" . /*$testVideo .*/ $htmlContent . "<p>---</p><p><a href=\"" . $permalink . "\">" . "Original page</a></p>";

       // $link = home_url( "/sw/v1/comments/{$post->post_name}");
        $panels = stwbplgn_get_panels($post);

        $connectionsSection = '';
        if(!empty($connections_info)){
            $connectionsSection = '<connections>' . $connections_info . '</connections>';

        }

        // Output the simplified content
        header('Content-Type: text/plain');
        echo '<hdoc>' . $panels . '<html>' . $finalContent . '</html>' . $connectionsSection . '</hdoc>';
    } else {
        // Handle post not found
        status_header(404);
        echo 'Page not found';
    }
}


function stwbplgn_modify_internal_links_in_html($htmlContent) {
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


function stwbplgn_modify_external_links_in_html($htmlContent) {
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



