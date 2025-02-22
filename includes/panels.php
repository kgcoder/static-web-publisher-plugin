<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbplgn_get_panels($post) {

    $permalink = get_permalink($post->ID);

    $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);

    $comments_link = home_url( "/comments{$path_part}");

    $settings = get_option('stwbplgn_settings', array(
        'global_background_color' => '',
        'global_text_color' => '',
        'info_link_variant' => 'none',
        'user_defined_info_url' => '',
        'side_panel_on_the_left' => false,
        'modify_internal_links' => false,
        'modify_external_links' => false,
        'top_panel' => array(
            'top_background_color' => '',
            'top_text_color' => '',
            'main_link' => '',
            'main_title' => '', 
            'logo_url' => '', 
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_background_color' => '',
            'bottom_text_color' => '',
            'bottom_message' => '',
            'sections' => array()
        ),
    ));

    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];

    $main_link = $top_panel['main_link'];
    
    $site_name = $top_panel['main_title'];

    $logo_url = $top_panel['logo_url'];

    $global_background_color = $settings['global_background_color'];

    $global_text_color = $settings['global_text_color'];

    $top_background_color = $top_panel['top_background_color'];

    $top_text_color = $top_panel['top_text_color'];

    $bottom_background_color = $bottom_panel['bottom_background_color'];

    $bottom_text_color = $bottom_panel['bottom_text_color'];

    $bottom_message = $bottom_panel['bottom_message'];


    $should_show_top_panel = !empty($site_name) || !empty($main_link) || !empty($logo_url) || !empty($top_panel['links']);
    $should_show_side_panel = stwbplgn_has_comment_section($post);
    $should_show_bottom_panel = !empty($bottom_message) || !empty($bottom_panel['sections']);

    $should_show_panels = $should_show_top_panel || $should_show_side_panel || $should_show_bottom_panel;

    $side_panel_left = !!$settings['side_panel_on_the_left'];

    $side_panel_attribute = $side_panel_left ? ' side="left"' : '';
    
    if(!$should_show_panels){
        return '';
    }
    
    ob_start();

    ?>

<panels<?php 
if(!empty($global_background_color)){
    echo ' bgColor="' . esc_attr($global_background_color) . '"';
}
if(!empty($global_text_color)){
    echo ' textColor="' . esc_attr($global_text_color) . '"';
}
?>>
<?php if($should_show_top_panel){ ?>
<top-panel<?php 
if(!empty($top_background_color)){
    echo ' bgColor="' . esc_attr($top_background_color) . '"';
}
if(!empty($top_text_color)){
    echo ' textColor="' . esc_attr($top_text_color) . '"';
}
?>>
<?php 
if(!empty($site_name)){
    echo '<site-name';
    if(!empty($main_link)){
        echo ' href="' . esc_url($main_link) . '"';
    }
    echo '>' . esc_attr($site_name) . '</site-name>';
}

if(!empty($logo_url)){
    echo '<logo src="' . esc_url($logo_url) . '"';
    if(!empty($main_link)){
        echo ' href="' . esc_url($main_link) . '"';
    }
    echo '/>';
}
?>
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
<?php if ($should_show_side_panel): ?>
<side-panel<?php
if(!empty($side_panel_left)){
    echo ' side="left"';
}
?>><?php echo esc_url($comments_link); ?></side-panel>
<?php endif; ?>
<?php if($should_show_bottom_panel){ ?>
<bottom-panel<?php 
if(!empty($bottom_background_color)){
    echo ' bgColor="' . esc_attr($bottom_background_color) . '"';
}
if(!empty($bottom_text_color)){
    echo ' textColor="' . esc_attr($bottom_text_color) . '"';
}
?>>
<?php
if (!empty($bottom_panel['sections'])) {
    foreach ($bottom_panel['sections'] as $section) {
        $section_title = $section['title'];
?>
<section<?php 
if(!empty($section_title)){
    echo ' title="' . esc_attr($section_title) . '"';
}
?>>
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
<?php if(!empty($bottom_message)){
    echo '<bottom-message>' . esc_attr($bottom_message) . '</bottom-message>';
}
?>
</bottom-panel>
<?php
}
?>

</panels><?php

    $output = ob_get_clean();

    return $output;

}


function stwbplgn_has_comment_section($post) {
    // Check if comments are globally allowed and post-specific status
    $global_comments_setting = get_option('default_comment_status');
    $theme_supports_comments = function_exists('comments_template');

    if ($post && $post->comment_status === 'open' && $global_comments_setting === 'open' && $theme_supports_comments) {
        return true;
    }

    return false;
}