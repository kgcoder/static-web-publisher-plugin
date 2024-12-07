
<?php

function custom_post_endpoints_add_link_to_content( $content ) {
    global $post;
    global $wp_query;


    if (( is_single() || is_page()) && $post && ('post' === $post->post_type || 'page' === $post->post_type) ) {

        $permalink = get_permalink($post->ID);

       $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);


        $link = preg_replace('/^http/', "sw", home_url( "/sw{$path_part}"));
     

        $simplified_link = sprintf('<a href="%s">[SW]</a>',$link);

        // Get the position setting (assuming you have a setting for this)
        $position = get_option( 'custom_post_endpoints_button_position', 'bottom' );

        $info_url = "https://google.com";

        $info_link = sprintf('<a href="%s" target="_blank">Learn about Static Web</a>',$info_url);

        $links_paragraph = '<p>' . $simplified_link . ' ' . $info_link . '</p>';


        if ( 'top' === $position ) {
            $content = $links_paragraph . $content;
        } elseif ( 'bottom' === $position ) {
            $content .= $links_paragraph;
        }
    }

    return $content;
}

add_filter( 'the_content', 'custom_post_endpoints_add_link_to_content' );

?>