
<?php

function custom_post_endpoints_add_link_to_content( $content ) {
    global $post;
    global $wp_query;


    if (( is_single() || is_page()) && $post && ('post' === $post->post_type || 'page' === $post->post_type) ) {

        $permalink = get_permalink($post->ID);

       $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);


        $link = preg_replace('/^http/', "sw", home_url( "/sw{$path_part}"));
     

        $icon = static_web_plugin_add_icon_with_srcset('sw_download_logo');

        $link_text = 'Download this page using Static Web';
        $simplified_link = '<a title="' . $link_text . '" href="' . $link . '" rel="alternate">' . $icon . '</a>';

        // Get the position setting (assuming you have a setting for this)
        $position = get_option( 'custom_post_endpoints_button_position', 'bottom' );

        $user_defined_info_url = get_option('static_web_plugin_settings')['user_defined_info_url'] ?? '';

    
        $default_info_url = "https://reinventingtheweb.com/how-to-use-sw-links/";

        $info_url = !empty($user_defined_info_url) ? $user_defined_info_url : $default_info_url;

        $question_icon = static_web_plugin_add_icon_with_srcset('sw_question');

        $info_link_text = 'Learn about Static Web';

        $info_link = '<a title="' . $info_link_text . '" href="' . $info_url . '" rel="nofollow noopener" target="_blank">' . $question_icon . '</a>';


        $links_paragraph = '<p>' . $simplified_link . '&nbsp;' . $info_link . '</p>';


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