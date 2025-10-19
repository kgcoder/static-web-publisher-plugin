<?php

/*
Plugin Name: Static Web Publisher
Description: Publishes your posts and pages on the Static Web
Version: 4.0.0
Author: Karen Grigorian
Author URI: https://github.com/kgcoder
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.1
Tested up to: 6.8
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/page-methods.php';
require_once plugin_dir_path(__FILE__) . 'includes/comments-json.php';
require_once plugin_dir_path(__FILE__) . 'includes/panels.php';
require_once plugin_dir_path(__FILE__) . 'includes/hdoc.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';




// Run on plugin activation
function stwbpb_activate() {
    stwbpb_custom_post_endpoints_rewrite_rules();
    flush_rewrite_rules(); // Refresh permalinks
    // Initialize default top_panel settings if all are empty
    $settings = get_option('stwbpb_settings', array());
    if (is_string($settings)) {
        $decoded = json_decode($settings, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $settings = $decoded;
        } else {
            $settings = array();
        }
    }
    if (!is_array($settings)) {
        $settings = array();
    }
    if (!isset($settings['top_panel']) || !is_array($settings['top_panel'])) {
        $settings['top_panel'] = array();
    }
    $top_panel = $settings['top_panel'];
    $main_link = isset($top_panel['main_link']) ? trim($top_panel['main_link']) : '';
    $main_title = isset($top_panel['main_title']) ? trim($top_panel['main_title']) : '';
    $logo_url = isset($top_panel['logo_url']) ? trim($top_panel['logo_url']) : '';

    if ($main_link === '' && $main_title === '' && $logo_url === '') {
        $settings['comments_title'] = 'Comments';
        $settings['no_comments_message'] = 'No comments yet';
        $settings['top_panel']['main_title'] = get_bloginfo('name');
        $settings['top_panel']['main_link'] = home_url();
        $settings['top_panel']['links'] = array(
            array(
                'url' => 'http://OP',
                'text' => 'Original page'
            )
        );
        update_option('stwbpb_settings', $settings);
    }
}
register_activation_hook(__FILE__, 'stwbpb_activate');

// Run on plugin deactivation (optional cleanup)
function stwbpb_deactivate() {
    flush_rewrite_rules(); // Clean up permalinks
}
register_deactivation_hook(__FILE__, 'stwbpb_deactivate');

function stwbpb_uninstall() {
    delete_option('stwbpb_settings');
}
register_uninstall_hook(__FILE__, 'stwbpb_uninstall');



function stwbpb_custom_post_endpoints_rewrite_rules() {

    $settings = get_option('stwbpb_settings', []);
    $prefix = isset($settings['rewrite_prefix']) && $settings['rewrite_prefix'] !== ''
        ? $settings['rewrite_prefix']
        : 'sw';

    add_rewrite_rule(
        '^json-comments/?(.+)?$',
        'index.php?json_comments_custom_matches=1',
        'top'
    );

    add_rewrite_rule(
        '^' . $prefix . '/(.+)?$',
        'index.php?sw_custom_matches=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^' . $prefix . '/?$',
        'index.php?sw_custom_matches=main_page',
        'top'
    );

}
add_action('init', 'stwbpb_custom_post_endpoints_rewrite_rules');

function stwbpb_custom_post_endpoints_query_vars($query_vars) {
    $query_vars[] = 'sw_custom_matches';
    $query_vars[] = 'comments_custom_matches';
    $query_vars[] = 'json_comments_custom_matches';

    return $query_vars;
}
add_filter('query_vars', 'stwbpb_custom_post_endpoints_query_vars');


function stwbpb_custom_post_endpoints_template_redirect() {
    global $wp_query;

    $permalink_structure = get_option( 'permalink_structure' );

    if (empty($permalink_structure) && isset($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), '/sw/') !== false && isset($wp_query->query_vars['p'])) {

        $post_id = (int) $wp_query->query_vars['p'];
        
        $expected_path1 = '/sw/?p=' . $post_id;
        $expected_path2 = '/sw/?page_id=' . $post_id;
        
        
        $current_path = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        
        if ($current_path === $expected_path1 || $current_path === $expected_path2) {
            $post = get_post($post_id);
            stwbpb_send_hdoc_for_post($post);
            exit;
        }


     
        exit;
    }

   


    if (isset($wp_query->query_vars['json_comments_custom_matches'])) {
        stwbpb_send_comments_json_from_post();
        exit;
    }


    if (!empty($permalink_structure) && isset($wp_query->query_vars['sw_custom_matches'])) {
        $path = $wp_query->query_vars['sw_custom_matches'];

      

        if($path === 'main_page'){

            

            if (get_option('show_on_front') === 'page') {
                $front_page_id = get_option('page_on_front');
                $post = get_post($front_page_id);
    
                if ($post) {
                    // Front page content found, send it
                    stwbpb_send_hdoc_for_post($post);
                } else {
                    // Front page set, but no content found, send 404
                    wp_die('Page not found', '', array('response' => 404));
                }
            } else {
                // No front page set, return 404
                wp_die('Page not found', '', array('response' => 404));
            }
          exit;

        }else if (strpos($permalink_structure, '%post_id%') !== false) {
            // Get the current path
            $current_path = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            $site_url = home_url(); // Base site URL
            $path = str_replace($site_url, '', $current_path);
        
            $settings = get_option('stwbpb_settings', array());
            $rewrite_prefix = $settings['rewrite_prefix'] ?? 'sw';
            // Generate a regex based on the permalink structure
            $pattern = preg_quote($permalink_structure, '/'); // Escape all special characters except for '%'
            $pattern = '\/' . $rewrite_prefix . str_replace('%post_id%', '(\d+)\/?', $pattern); // Replace %post_id% with (\d+)
        
         
            if (preg_match("/^" . $pattern . "$/", $path, $matches)) {
                $post_id = $matches[1]; // Extracted post ID
                $slug = $post_id;
            } else {
                // Remove any trailing slash if it exists
                $path = rtrim($path, '/');
                // Use basename to get the last part of the path
                $slug = basename($path);

            }
        }else{
            $parsed_path = wp_parse_url($path, PHP_URL_PATH);

            // Break the path into parts
            $path_parts = explode('/', trim($parsed_path, '/'));

            // Assuming the slug is the last part of the path
            $slug = end($path_parts);
          //  $slug = basename(get_permalink());
        }
        //echo $path;
        // echo $slug;
        // exit;
  
        if (is_numeric($slug)) {
            $post_id = (int)$slug;
            $post = get_post($post_id);

            stwbpb_send_hdoc_for_post($post);
          

        }else{
            $post = get_page_by_path($slug, OBJECT, array('post','page'));
       
            stwbpb_send_hdoc_for_post($post);
           

        }

        exit;
    }

}
add_action('template_redirect', 'stwbpb_custom_post_endpoints_template_redirect');



add_filter('the_content', function($content) {
    if (is_admin()) return $content;
    $settings = get_option('stwbpb_settings', array());
    if(isset($settings['serve_hdoc_from_different_url'])){
        return;
    }

    // Only for single post/page
    if (is_singular(['post', 'page'])) {
        return '<div id="temp-hdoc-content">' . $content . '</div>';
    }

    return $content;
});

add_action('template_redirect', function() {
    // Only on single posts or pages
    if (!is_singular(['post', 'page'])) return;
    $settings = get_option('stwbpb_settings', array());
    if(isset($settings['serve_hdoc_from_different_url'])){
        return;
    }

    ob_start(function($html) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // ----- Handle content -----
        $contentTemp = $dom->getElementById('temp-hdoc-content');
        if ($contentTemp && $contentTemp->parentNode) {
            $parent = $contentTemp->parentNode;

            // Mark parent container
            $existing = $parent->getAttribute('class');
            $parent->setAttribute('class', trim($existing . ' hdoc-content'));

            // Unwrap temp div
            while ($contentTemp->firstChild) {
                $parent->insertBefore($contentTemp->firstChild, $contentTemp);
            }
            $parent->removeChild($contentTemp);
        }

        return $dom->saveHTML();
    });
});




add_action('wp_body_open', 'stwbpb_output_xml');
function stwbpb_output_xml() {
    if (!is_singular()) return;
    global $post;
    if (!$post) return;


    $settings = get_option('stwbpb_settings', array());
    if(isset($settings['serve_hdoc_from_different_url'])){
        return;
    }


    $display_author_name = isset($settings['display_author_name']) ? $settings['display_author_name'] : '';
    $display_publish_date = isset($settings['display_publish_date']) ? $settings['display_publish_date'] : '';
    $removal_selectors = isset($settings['removal_selectors']) ? $settings['removal_selectors'] : '';

    $header = [
        'h1'  => get_the_title($post),
    ];

    if(!empty($display_author_name)){
        $author_id   = get_post_field('post_author', $post->ID);
        $author_name = get_the_author_meta('display_name', $author_id);
        $header['author'] = $author_name;
    }

    if(!empty($display_publish_date)){
        $date = get_the_date(get_option('date_format'), $post->ID);
        $header['date'] = $date;
    }

    $panels_escaped = stwbpb_get_panels($post);

    $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);
    $connectionsSection = '';
    if (!empty($connections_info)) {
        $connectionsSection = '<docs>' . $connections_info . '</docs>';
    }

 

    $panels_obj = simplexml_load_string($panels_escaped, "SimpleXMLElement", LIBXML_NOCDATA);
    $connections_obj = simplexml_load_string($connectionsSection, "SimpleXMLElement", LIBXML_NOCDATA);


    if(!empty($panels_obj)){
        $panels_array = xml_to_array_with_attributes($panels_obj);
    }

    $connections_obj = simplexml_load_string($connectionsSection, "SimpleXMLElement", LIBXML_NOCDATA);

    $connections_array = [];

    if(!empty($connections_obj)){
        // Handle multiple <doc> elements
        foreach ($connections_obj->doc as $doc) {
            $connections_array[] = xml_to_array_with_attributes($doc, 'doc');
        }

    }

    $data = [];

    
    if (!empty($header)) {
        $data['header'] = $header;
    }
    
    if (!empty($removal_selectors)) {
        $data['removal-selectors'] = $removal_selectors;
    }

    if (!empty($panels_array)) {
        $data['panels'] = $panels_array;
    }


    if (!empty($connections_array)) {
        $data['connections'] = $connections_array;
    }

    if (empty($data)) return;


    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    // Replace any accidental </script> in JSON strings
    $json = str_replace('</script>', '<\/script>', $json);
   
    // Output JSON directly
    // Note: Safe because type="application/json" is treated as literal text
    echo '<script type="application/json" id="hdoc-data">' . PHP_EOL;
    echo $json;
    echo PHP_EOL .'</script>';
}


function xml_to_array_with_attributes($xml, $parent_name = '') {
    $arr = [];

    // Include attributes
    foreach ($xml->attributes() as $attr_name => $attr_value) {
        $arr[$attr_name] = (string)$attr_value;
    }

    // Include children
    foreach ($xml->children() as $child_name => $child) {
        $child_array = xml_to_array_with_attributes($child, $child_name);

        // Special handling: if <a> inside <top>, push to 'links' array
        if ($parent_name === 'top' && $child_name === 'a') {
            if (!isset($arr['links'])) {
                $arr['links'] = [];
            }
            $arr['links'][] = $child_array;
            continue; // skip adding as 'a' key
        }

        if ($parent_name === 'bottom' && $child_name === 'section') {
            if (!isset($arr['sections'])) {
                $arr['sections'] = [];
            }
            $arr['sections'][] = $child_array;
            continue; // skip adding as 'section' key
        }

        if ($parent_name === 'section' && $child_name === 'a') {
            if (!isset($arr['links'])) {
                $arr['links'] = [];
            }
            $arr['links'][] = $child_array;
            continue; // skip adding as 'a' key
        }

        // If multiple children with same name, make it numeric array
        if (isset($arr[$child_name])) {
            if (!is_array($arr[$child_name]) || !isset($arr[$child_name][0])) {
                $arr[$child_name] = [$arr[$child_name]];
            }
            $arr[$child_name][] = $child_array;
        } else {
            $arr[$child_name] = $child_array;
        }
    }

    // Include text content
    $text = (string)$xml;
    if ($text !== '' && trim($text) !== '') {
        if ($parent_name === 'doc') {
            // Split by newlines, trim, remove empty lines
            $lines = array_filter(array_map('trim', explode("\n", $text)), function($line) {
                return $line !== '';
            });
            $arr['flinks'] = array_values($lines);
        } elseif ($parent_name === 'a' || $parent_name === 'site-name') {
            $arr['text'] = $text;
        } elseif ($parent_name === 'comments') {
            $arr['url'] = $text;
        } elseif ($parent_name === 'bottom-message') {
            return $text;
        }else {
            $arr['_text'] = $text;
        }
    }

    return $arr;
}



