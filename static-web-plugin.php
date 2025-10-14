<?php

/*
Plugin Name: Static Web Publisher
Description: Publishes your posts and pages on the Static Web
Version: 3.0.0
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

require_once plugin_dir_path(__FILE__) . 'includes/download-link.php';
require_once plugin_dir_path(__FILE__) . 'includes/comments-page.php';
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

  
    add_rewrite_rule(
        '^sw-comments/(.+)?$',
        'index.php?comments_custom_matches=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^json-comments/?(.+)?$',
        'index.php?json_comments_custom_matches=1',
        'top'
    );

    add_rewrite_rule(
        '^sw/(.+)?$',
        'index.php?sw_custom_matches=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^sw/?$',
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

   

    if (isset($wp_query->query_vars['comments_custom_matches'])) {
        $path = $wp_query->query_vars['comments_custom_matches'];
        
        if (strpos($permalink_structure, '%post_id%') !== false) {
            // Get the current path
            $current_path = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            $site_url = home_url(); // Base site URL
            $path = str_replace($site_url, '', $current_path);
        
            
            // Generate a regex based on the permalink structure
            $pattern = preg_quote($permalink_structure, '/'); // Escape all special characters except for '%'
            $pattern = '\/sw' . str_replace('%post_id%', '(\d+)\/?', $pattern); // Replace %post_id% with (\d+)
        
         
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

        if (is_numeric($slug)) {
            $post_id = (int)$slug;
            $post = get_post($post_id);
            if ($post) {
                stwbpb_send_comments_from_post($post);
            } else {
                echo 'Post not found by ID';
            }

        }else{
            $post = get_page_by_path($slug, OBJECT, array('post','page'));
            if ($post) {
                stwbpb_send_comments_from_post($post);
            } else {
                echo 'Post not found by ID';
            }

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
        
            
            // Generate a regex based on the permalink structure
            $pattern = preg_quote($permalink_structure, '/'); // Escape all special characters except for '%'
            $pattern = '\/sw' . str_replace('%post_id%', '(\d+)\/?', $pattern); // Replace %post_id% with (\d+)
        
         
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


