<?php

/*
Plugin Name: Static Web Publisher
Description: Publishes your posts and pages on the Static Web
Version: 5.1.2
Author: Karen Grigorian
Author URI: https://github.com/kgcoder
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.2
Tested up to: 7.0
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/page-methods.php';
require_once plugin_dir_path(__FILE__) . 'includes/comments-json.php';
require_once plugin_dir_path(__FILE__) . 'includes/comment-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/doc-files.php';
require_once plugin_dir_path(__FILE__) . 'includes/panels.php';
require_once plugin_dir_path(__FILE__) . 'includes/hdoc.php';
require_once plugin_dir_path(__FILE__) . 'includes/cdoc.php';
require_once plugin_dir_path(__FILE__) . 'includes/condoc.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/sidebar-variants.php';
require_once plugin_dir_path(__FILE__) . 'includes/proxy.php';




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
        $settings['comments_title']            = 'Comments';
        $settings['no_comments_message']       = 'No comments yet';
        $settings['reply_button_label']        = 'Reply';
        $settings['leave_comment_label']       = 'Leave a comment';
        $settings['form_title']                = 'Leave a comment';
        $settings['replying_to_label']         = 'Replying to %s';
        $settings['commenting_on_label']       = 'Commenting on: %s';
        $settings['name_label']                = 'Name';
        $settings['email_label']               = 'Email';
        $settings['comment_label']             = 'Comment';
        $settings['submit_button_label']       = 'Post comment';
        $settings['submitted_title']           = 'Comment submitted';
        $settings['thank_you_message']         = 'Thank you for your comment!';
        $settings['awaiting_approval_message'] = 'It will appear once approved.';
        $settings['error_security']            = 'Security check failed. Please try again.';
        $settings['error_closed']              = 'Commenting is closed for this post.';
        $settings['error_invalid_parent']      = 'Invalid parent comment.';
        $settings['error_name_required']       = 'Please enter your name.';
        $settings['error_invalid_email']       = 'Please enter a valid email address.';
        $settings['error_comment_required']    = 'Please enter a comment.';
        $settings['error_save_failed']         = 'Could not save your comment. Please try again.';
        $settings['top_panel']['main_title']   = get_bloginfo('name');
        $settings['top_panel']['main_link']    = home_url();

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

    $settings = get_option('stwbpb_settings', array());

    add_rewrite_rule(
        '^json-comments/?(.+)?$',
        'index.php?json_comments_custom_matches=1',
        'top'
    );

    add_rewrite_rule(
        '^sw-proxy/?$',
        'index.php?sw_proxy_request=1',
        'top'
    );

    add_rewrite_rule(
        '^sw-comment-form/?$',
        'index.php?sw_comment_form_request=1',
        'top'
    );

    add_rewrite_rule(
        '^static/(.+)$',
        'index.php?doc_viewer_matches=$matches[1]',
        'top'
    );

}
add_action('init', 'stwbpb_custom_post_endpoints_rewrite_rules');

function stwbpb_custom_post_endpoints_query_vars($query_vars) {
    $query_vars[] = 'doc_viewer_matches';
    $query_vars[] = 'comments_custom_matches';
    $query_vars[] = 'json_comments_custom_matches';
    $query_vars[] = 'sw_proxy_request';
    $query_vars[] = 'sw_comment_form_request';

    return $query_vars;
}
add_filter('query_vars', 'stwbpb_custom_post_endpoints_query_vars');


add_filter('user_trailingslashit', function($url, $type){
    if (strpos($url, 'static/') !== false) {
        return untrailingslashit($url);
    }
    return $url;
}, 10, 2);

function stwbpb_custom_post_endpoints_template_redirect() {
    global $wp_query;

    if (is_singular(['post', 'page'])) {
        global $post;
        if ($post && stwbpb_get_doc_effective_display_mode($post) === 'standalone_doc') {
            $type = stwbpb_get_effective_doc_type($post);
            if ($type === 'CDOC') {
                stwbpb_send_cdoc_for_post($post);
            } elseif ($type === 'CONDOC') {
                stwbpb_send_condoc_for_post($post);
            } else {
                stwbpb_send_hdoc_for_post($post);
            }
            exit;
        }

        $mode = $post ? stwbpb_get_doc_effective_display_mode($post) : '';
        if ($mode !== 'doc_in_reader' && $mode !== 'standalone_doc') {
            ob_start(function($html) {
                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                $contentTemp = $dom->getElementById('hdoc-content');
                if ($contentTemp && $contentTemp->parentNode) {
                    $parent = $contentTemp->parentNode;

                    $existing = $parent->getAttribute('class');
                    $parent->setAttribute('class', trim($existing . ' hdoc-content'));

                    while ($contentTemp->firstChild) {
                        $parent->insertBefore($contentTemp->firstChild, $contentTemp);
                    }
                    $parent->removeChild($contentTemp);
                }

                return $dom->saveHTML();
            });
        }
    }


    if (isset($wp_query->query_vars['sw_proxy_request'])) {
        stwbpb_proxy_fetch();
        exit;
    }

    if (isset($wp_query->query_vars['json_comments_custom_matches'])) {
        stwbpb_send_comments_json_from_post();
        exit;
    }

    if (isset($wp_query->query_vars['sw_comment_form_request'])) {
        stwbpb_handle_comment_form();
        exit;
    }


    if (isset($wp_query->query_vars['doc_viewer_matches'])) {
        $path = $wp_query->query_vars['doc_viewer_matches'];
        stwbpb_send_doc_file($path);
        exit;
    }


}
add_action('template_redirect', 'stwbpb_custom_post_endpoints_template_redirect');



add_action('save_post', function($post_id) {
    delete_transient('swp_connections_' . $post_id);
});


add_filter('the_content', function($content) {
    if (is_admin()) return $content;
    $settings = get_option('stwbpb_settings', array());

    // Only for single post/page
    if (is_singular(['post', 'page'])) {
        return '<div id="hdoc-content">' . $content . '</div>';
    }

    return $content;
});





add_filter('template_include', function ($template) {

    if (is_admin()) return $template;
    if (!is_singular(['post', 'page'])) return $template;

    $settings = get_option('stwbpb_settings', []);

    global $post;
    if (!$post) return $template;

    $mode = stwbpb_get_doc_effective_display_mode($post);
    if ($mode === 'doc_in_reader') {
        return plugin_dir_path(__FILE__) . 'templates/reader-template.php';
    }

    return $template;
});



add_action('wp_enqueue_scripts', function () {
    if (is_admin() || !is_singular(['post', 'page'])) return;
    global $post;
    if (!$post) return;
    if (stwbpb_get_doc_effective_display_mode($post) !== 'doc_in_reader') return;

    $reader_url  = plugins_url('reader/', __FILE__);
    $reader_path = plugin_dir_path(__FILE__) . 'reader/';
    $dist_url    = plugins_url('dist/', __FILE__);
    $dist_path   = plugin_dir_path(__FILE__) . 'dist/';

    wp_enqueue_style('swp-reader',      $reader_url . 'reader.css',       [], filemtime($reader_path . 'reader.css'));
    wp_enqueue_style('swp-export-page', $reader_url . 'ExportPage.css',   [], filemtime($reader_path . 'ExportPage.css'));
    wp_enqueue_style('swp-page-info',   $reader_url . 'PageInfo.css',     [], filemtime($reader_path . 'PageInfo.css'));
    wp_enqueue_style('swp-theme-light', $reader_url . 'themes/light.css', [], filemtime($reader_path . 'themes/light.css'));
    wp_enqueue_style('swp-theme-dark',  $reader_url . 'themes/dark.css',  [], filemtime($reader_path . 'themes/dark.css'));
    wp_enqueue_style('swp-theme-sepia', $reader_url . 'themes/sepia.css', [], filemtime($reader_path . 'themes/sepia.css'));

    if (defined('WP_DEBUG') && WP_DEBUG) {
        $js_url = $reader_url . 'readerStartUp.js';
        $js_ver = filemtime($reader_path . 'readerStartUp.js');
    } else {
        $js_url = $dist_url . 'reader.bundle.min.js';
        $js_ver = filemtime($dist_path . 'reader.bundle.min.js');
    }
    wp_enqueue_script('swp-reader-js', $js_url, [], $js_ver, false);

    wp_add_inline_script('swp-reader-js', sprintf(
        'window.vcReaderData = { assetsUrl: %s, proxyUrl: %s };',
        wp_json_encode($reader_url . 'images/'),
        wp_json_encode(home_url('/sw-proxy/'))
    ), 'before');
});

add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'swp-reader-js') {
        return str_replace('<script ', '<script type="module" ', $tag);
    }
    return $tag;
}, 10, 2);


add_action('wp_footer', 'stwbpb_output_xml', 9999);
function stwbpb_output_xml() {
    if (!is_singular()) return;
    global $post;
    if (!$post) return;




    $settings = get_option('stwbpb_settings', array());

    $mode = stwbpb_get_doc_effective_display_mode($post);
    if ($mode === 'standalone_doc') return;
    $type = stwbpb_get_effective_doc_type($post);
    if ($type === 'CDOC' || $type === 'CONDOC') return;

    $removal_selectors = isset($settings['removal_selectors']) ? $settings['removal_selectors'] : '';

    $header = [
        'h1'  => get_the_title($post),
    ];

    if (stwbpb_get_effective_author_display($post) === 'show') {
        $author_id = get_post_field('post_author', $post->ID);
        $header['author'] = get_the_author_meta('display_name', $author_id);
    }

    if (stwbpb_get_effective_date_display($post) === 'show') {
        $header['date'] = get_the_date(get_option('date_format'), $post->ID);
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
        $panels_array = stwbpb_xml_to_array_with_attributes($panels_obj);
    }

    $connections_obj = simplexml_load_string($connectionsSection, "SimpleXMLElement", LIBXML_NOCDATA);

    $connections_array = [];

    if(!empty($connections_obj)){
        // Handle multiple <doc> elements
        foreach ($connections_obj->doc as $doc) {
            $connections_array[] = stwbpb_xml_to_array_with_attributes($doc, 'doc');
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

    if ($mode === 'embedded_hdoc_forced' || $mode === 'doc_in_reader') {
        $data['forced'] = true;
    }

    $rep_policy = stwbpb_get_effective_republishing_policy($post);
    if ($rep_policy === 'explicit_allow') {
        $data['republishing-policy'] = 'allow';
    } elseif ($rep_policy === 'prohibit') {
        $data['republishing-policy'] = 'do-not-republish';
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    // Replace any accidental </script> in JSON strings
    $json = str_replace('</script>', '<\/script>', $json);
   
    // Output JSON directly
    echo '<script type="application/json" id="hdoc-data">' . PHP_EOL;
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe because type="application/json" is treated as literal text
    echo $json;
    echo PHP_EOL .'</script>';

    // exit;
}


function stwbpb_xml_to_array_with_attributes($xml, $parent_name = '') {
    $arr = [];

    // Include attributes
    foreach ($xml->attributes() as $attr_name => $attr_value) {
        $arr[$attr_name] = (string)$attr_value;
    }

    // Include children
    foreach ($xml->children() as $child_name => $child) {
        $child_array = stwbpb_xml_to_array_with_attributes($child, $child_name);

        // Special handling: if <a> inside <top>, push to 'links' array
        if ($parent_name === 'top' && $child_name === 'a') {
            if (!isset($arr['links'])) {
                $arr['links'] = [];
            }
            $arr['links'][] = $child_array;
            continue; // skip adding as 'a' key
        }

        if ($parent_name === 'sidebar') {
            $child_array['type'] = $child_name;
            if (!isset($arr['items'])) {
                $arr['items'] = [];
            }
            $arr['items'][] = $child_array;
            continue;
        }

        if ($parent_name === 'links' && $child_name === 'a') {
            if (!isset($arr['items'])) {
                $arr['items'] = [];
            }
            $arr['items'][] = $child_array;
            continue;
        }

        if ($parent_name === 'recent-comments' && $child_name === 'comment') {
            if (!isset($arr['comments'])) {
                $arr['comments'] = [];
            }
            $arr['comments'][] = $child_array;
            continue;
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
        } elseif ($parent_name === 'prev' || $parent_name === 'next') {
            $arr['title'] = $text;
        } elseif ($parent_name === 'bottom-message') {
            return $text;
        }else {
            $arr['_text'] = $text;
        }
    }

    return $arr;
}


