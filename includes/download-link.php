<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function custom_post_endpoints_add_meta_box() {
    add_meta_box(
        'custom_post_endpoints_meta_box',
        'Static Web Link Settings',
        'custom_post_endpoints_meta_box_callback',
        array('post', 'page'), // Enable for posts and pages
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_post_endpoints_add_meta_box');

function custom_post_endpoints_meta_box_callback($post) {
    $value = get_post_meta($post->ID, '_disable_static_web_link', true);
    $disable_original_page_value = get_post_meta($post->ID, '_disable_original_page', true);

    $connections_info = get_post_meta($post->ID, '_static_web_connections_info', true);

    wp_nonce_field('custom_post_endpoints_meta_box_nonce', 'custom_post_endpoints_nonce');
    ?>
    <label for="disable_static_web_link">
        <input type="checkbox" name="disable_static_web_link" id="disable_static_web_link" value="1" <?php checked($value, '1'); ?> />
        Disable Static Web Link on this post/page
    </label>
    <br><br>
    <label for="disable_original_page">
        <input type="checkbox" name="disable_original_page" id="disable_original_page" value="1" <?php checked($disable_original_page_value, '1'); ?> />
        Disable the original post/page
    </label>
    <br><br>
    <label for="static_web_connections_info"><strong>Connections Info:</strong></label>
    <textarea name="static_web_connections_info" id="static_web_connections_info" rows="3" style="width:100%;"><?php echo esc_textarea($connections_info); ?></textarea>
    <p class="description">Add connections info</p>
    <?php
}

function custom_post_endpoints_save_meta_box($post_id) {
    if (!isset($_POST['custom_post_endpoints_nonce']) || !wp_verify_nonce($_POST['custom_post_endpoints_nonce'], 'custom_post_endpoints_meta_box_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $value = isset($_POST['disable_static_web_link']) ? '1' : '';
    update_post_meta($post_id, '_disable_static_web_link', $value);

    $value = isset($_POST['disable_original_page']) ? '1' : '';
    update_post_meta($post_id, '_disable_original_page', $value);

    

    $allowed_tags = array(
        'doc'  => array('url' => true, 'title' => true, 'hash' => true), 
    );

    if (isset($_POST['static_web_connections_info'])) {
        update_post_meta($post_id, '_static_web_connections_info', wp_kses($_POST['static_web_connections_info'], $allowed_tags));
    }
}
add_action('save_post', 'custom_post_endpoints_save_meta_box');




function custom_redirect_logic() {
    if (is_single() || is_page()) {  // Applies to posts and pages

        global $post;
        if (!$post) return; // Prevent errors if $post is undefined

        if (get_post_meta($post->ID, '_disable_original_page', true) === '1') {
            $permalink = get_permalink($post->ID);
        
            $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);
            
            
            $link = preg_replace('/^http/', "sw", home_url( "/sw{$path_part}"));
            wp_redirect($link);
            exit;
        }

        
    
    }
}
add_action('template_redirect', 'custom_redirect_logic');




function custom_post_endpoints_add_link_to_content( $content ) {
    global $post;
    global $wp_query;


    if (( is_single() || is_page()) && $post && ('post' === $post->post_type || 'page' === $post->post_type) ) {

        // Check if the user has disabled the link for this post
        if (get_post_meta($post->ID, '_disable_static_web_link', true) === '1') {
            return $content;
        }


        
        
        $permalink = get_permalink($post->ID);
        
        $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);
        
        
        $link = preg_replace('/^http/', "sw", home_url( "/sw{$path_part}"));
        

        $icon = static_web_plugin_add_icon_with_srcset('sw_download_logo');

        $link_text = 'Download this page using Static Web';
        $simplified_link = '<a class="swp-link" style="border:none;box-shadow:none;" title="' . $link_text . '" href="' . $link . '" rel="alternate">' . $icon . '</a>';

        // Get the position setting (assuming you have a setting for this)
        $position = get_option( 'custom_post_endpoints_button_position', 'bottom' );

       $settings = get_option('static_web_plugin_settings', array()); // Ensure a default empty array
        $user_defined_info_url = isset($settings['user_defined_info_url']) ? $settings['user_defined_info_url'] : '';
    
        $info_link_variant = $settings['info_link_variant'];

    
        $default_info_url = "https://reinventingtheweb.com/how-to-use-sw-links/";


        $info_url = $info_link_variant === 'custom' ? $user_defined_info_url : $default_info_url;

        $question_icon = static_web_plugin_add_icon_with_srcset('sw_question');

        $info_link_text = 'Learn about Static Web';

        $info_link = '';
        if($info_link_variant !== 'none'){
            $info_link = '&nbsp;<a class="swp-link" style="border:none;box-shadow:none;" title="' . $info_link_text . '" href="' . $info_url . '" rel="nofollow noopener" target="_blank">' . $question_icon . '</a>';
        }


        $links_paragraph = '<div style="display:flex;margin-bottom:10px;">' . $simplified_link . '&nbsp;' . $info_link . '</div>';


        if ( 'top' === $position ) {
            $content = $links_paragraph . $content;
        } elseif ( 'bottom' === $position ) {
            $content .= $links_paragraph;
        }
    }

    return $content;
}

add_filter( 'the_content', 'custom_post_endpoints_add_link_to_content' );

function static_web_plugin_add_icon_with_srcset($filename) {
    $icon_1x = plugins_url('assets/images/' . $filename . '.png', __FILE__);
    $icon_2x = plugins_url('assets/images/' . $filename . '@2x.png', __FILE__);
    $icon_3x = plugins_url('assets/images/' . $filename . '@3x.png', __FILE__);

    ob_start();
    ?><img 
        src="<?php echo esc_url($icon_1x); ?>" 
        srcset="<?php echo esc_url($icon_1x); ?> 1x, 
                <?php echo esc_url($icon_2x); ?> 2x, 
                <?php echo esc_url($icon_3x); ?> 3x" 
        alt="My Icon" 
        style="width:24px; height:24px;" /><?php
    return ob_get_clean();
}


?>