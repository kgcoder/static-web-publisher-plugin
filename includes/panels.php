<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function get_panels($post) {

    $permalink = get_permalink($post->ID);

    $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);

    $comments_link = home_url( "/comments{$path_part}");

    $settings = get_option('static_web_plugin_settings', [
        'global_background_color' => '',
        'global_text_color' => '',
        'user_defined_info_url' => '',
        'side_panel_on_the_left' => false,
        'modify_internal_links' => false,
        'modify_external_links' => false,
        'top_panel' => [
            'top_background_color' => '',
            'top_text_color' => '',
            'main_link' => '',
            'main_title' => '', 
            'logo_url' => '', 
            'links' => []
        ],
        'bottom_panel' => [
            'bottom_background_color' => '',
            'bottom_text_color' => '',
            'bottom_message' => '',
            'sections' => []
        ],
    ]);

    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];

    $main_link = $top_panel['main_link'];
    $main_link_attribute = $main_link ? ' href="' . $main_link . '"' : '';
    
    $site_name = $top_panel['main_title'];
    $site_name_element = $site_name ? '<site-name' . $main_link_attribute . '>' . $site_name . '</site-name>' : '';

    $logo_url = $top_panel['logo_url'];
    $logo_url_element = $logo_url ? '<logo src="' . $logo_url . '"' . $main_link_attribute . '/>' : '';

    $global_background_color = $settings['global_background_color'];
    $global_background_color_attribute = $global_background_color ? ' bgColor="' . esc_attr($global_background_color) . '"' : '';

    $global_text_color = $settings['global_text_color'];
    $global_text_color_attribute = $global_text_color ? ' textColor="' . esc_attr($global_text_color) . '"' : '';

    $top_background_color = $top_panel['top_background_color'];
    $top_background_color_attribute = $top_background_color ? ' bgColor="' . esc_attr($top_background_color) . '"' : '';

    $top_text_color = $top_panel['top_text_color'];
    $top_text_color_attribute = $top_text_color ? ' textColor="' . esc_attr($top_text_color) . '"' : '';

    $bottom_background_color = $bottom_panel['bottom_background_color'];
    $bottom_background_color_attribute = $bottom_background_color ? ' bgColor="' . esc_attr($bottom_background_color) . '"' : '';

    $bottom_text_color = $bottom_panel['bottom_text_color'];
    $bottom_text_color_attribute = $bottom_text_color ? ' textColor="' . esc_attr($bottom_text_color) . '"' : '';

    $bottom_message = $bottom_panel['bottom_message'];
    $bottom_message_element = $bottom_message ? '<bottom-message>' . $bottom_message . '</bottom-message>' : '';


    $should_show_top_panel = !empty($site_name_element) || !empty($logo_url) || !empty($top_panel['links']);
    $should_show_bottom_panel = !empty($bottom_message) || !empty($bottom_panel['sections']);

    $should_show_panels = $should_show_top_panel || $should_show_bottom_panel;

    $side_panel_left = !!$settings['side_panel_on_the_left'];

    $side_panel_attribute = $side_panel_left ? ' side="left"' : '';
    
    if(!$should_show_panels){
        return '';
    }
    
    ob_start();

    ?>

<panels<?php echo $global_background_color_attribute; echo $global_text_color_attribute;?>>
<?php if($should_show_top_panel){ ?>
<top-panel<?php echo $top_background_color_attribute; echo $top_text_color_attribute;?>>
<?php echo $site_name_element; ?>

<?php echo $logo_url_element; ?>
<?php
if (!empty($top_panel['links'])) {
    foreach ($top_panel['links'] as $index => $link) {
        echo '<a href="' . esc_url($link['url']) . '">' . esc_html($link['text']) . '</a>' . PHP_EOL; 
    }
}
?>
</top-panel>
<?php
}
?>
<?php if (has_comment_section($post)): ?>
<side-panel<?php echo $side_panel_attribute; ?>><?php echo $comments_link; ?></side-panel>
<?php endif; ?>
<?php if($should_show_bottom_panel){ ?>
<bottom-panel<?php echo $bottom_background_color_attribute; echo $bottom_text_color_attribute;?>>
<?php
if (!empty($bottom_panel['sections'])) {
    foreach ($bottom_panel['sections'] as $section) {
        $section_title = $section['title'];
        $title_element =  $section_title ? '<title>' . $section_title . '</title>' : '';

        $title_attribute = $section_title ? ' title="' . $section_title . '"' : '';
?>
<section<?php echo $title_attribute; ?>>
<?php
if (!empty($section['links'])) {
    foreach ($section['links'] as $index => $link) {
        echo '<a href="' . esc_url($link['url']) . '">' . esc_html($link['text']) . '</a>' . PHP_EOL; 
    }
}
?>
</section>
<?php
    }
}
?>
<?php echo $bottom_message_element; ?>
</bottom-panel>
<?php
}
?>

</panels><?php

    $output = ob_get_clean();

    return $output;

}


function has_comment_section($post) {
    // Check if comments are globally allowed and post-specific status
    $global_comments_setting = get_option('default_comment_status');
    $theme_supports_comments = function_exists('comments_template');

    if ($post && $post->comment_status === 'open' && $global_comments_setting === 'open' && $theme_supports_comments) {
        return true;
    }

    return false;
}