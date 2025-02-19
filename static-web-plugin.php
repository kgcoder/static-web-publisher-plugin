<?php

/*
Plugin Name: Static Web Plugin
Description: Publishes your posts and pages on the Static Web
Version: 1.0
Author: Karen Grigorian
Author URI: https://github.com/kgcoder
*/

require_once plugin_dir_path(__FILE__) . 'includes/download-link.php';
require_once plugin_dir_path(__FILE__) . 'includes/comments-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/panels.php';
require_once plugin_dir_path(__FILE__) . 'includes/hdoc.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Run on plugin activation
function stwebplgn_activate() {
    stwebplgn_custom_post_endpoints_rewrite_rules();
    flush_rewrite_rules(); // Refresh permalinks
}
register_activation_hook(__FILE__, 'stwebplgn_activate');

// Run on plugin deactivation (optional cleanup)
function stwebplgn_deactivate() {
    flush_rewrite_rules(); // Clean up permalinks
}
register_deactivation_hook(__FILE__, 'stwebplgn_deactivate');


function stwebplgn_custom_post_endpoints_rewrite_rules() {

  
    add_rewrite_rule(
        '^comments/(.+)?$',
        'index.php?comments_custom_matches=$matches[1]',
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
add_action('init', 'stwebplgn_custom_post_endpoints_rewrite_rules');

function stwebplgn_custom_post_endpoints_query_vars($query_vars) {
    $query_vars[] = 'sw_custom_matches';
    $query_vars[] = 'comments_custom_matches';

    return $query_vars;
}
add_filter('query_vars', 'stwebplgn_custom_post_endpoints_query_vars');



// function strip_unwanted_tags($content, $allowed_tags = array()) {
//     // Create a string of allowed tags for use with wp_kses
//     $allowed_html = array();
//     foreach ($allowed_tags as $tag) {
//         $allowed_html[$tag] = array();
//     }
//     // Use wp_kses to filter the content
//     $content = wp_kses($content, $allowed_html);
//     return $content;
// }


function stwebplgn_custom_post_endpoints_template_redirect() {
    global $wp_query;

    $permalink_structure = get_option( 'permalink_structure' );

    if (empty($permalink_structure) && strpos( $_SERVER['REQUEST_URI'], '/sw/') !== false && isset($wp_query->query_vars['p'])) {

        $post_id = (int) $wp_query->query_vars['p'];
        
        $expected_path1 = '/sw/?p=' . $post_id;
        $expected_path2 = '/sw/?page_id=' . $post_id;
        
        
        $current_path = $_SERVER['REQUEST_URI'];
        
        if ($current_path === $expected_path1 || $current_path === $expected_path2) {
            $post = get_post($post_id);
            stwebplgn_send_hdoc_for_post($post);
            exit;
        }


     
        exit;
    }

   

    if (isset($wp_query->query_vars['comments_custom_matches'])) {
        $path = $wp_query->query_vars['comments_custom_matches'];
        
        if (strpos($permalink_structure, '%post_id%') !== false) {
            // Get the current path
            $current_path = $_SERVER['REQUEST_URI'];
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
                stwebplgn_send_comments_from_post($post);
            } else {
                echo 'Post not found by ID';
            }

        }else{
            $post = get_page_by_path($slug, OBJECT, array('post','page'));
            if ($post) {
                stwebplgn_send_comments_from_post($post);
            } else {
                echo 'Post not found by ID';
            }

        }
   
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
                    stwebplgn_send_hdoc_for_post($post);
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
            $current_path = $_SERVER['REQUEST_URI'];
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

            stwebplgn_send_hdoc_for_post($post);
          

        }else{
            $post = get_page_by_path($slug, OBJECT, array('post','page'));
       
            stwebplgn_send_hdoc_for_post($post);
           

        }

        exit;
    }

}
add_action('template_redirect', 'stwebplgn_custom_post_endpoints_template_redirect');


