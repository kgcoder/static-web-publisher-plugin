<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_get_panels($post) {

    $permalink = get_permalink($post->ID);

    $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);


    $comments_link = home_url( "/json-comments/?post={$post->ID}");

    $settings = get_option('stwbpb_settings', array(
        'removal_selectors' => '',
        'side_panel_on_the_left' => false,
        'comments_title' => '',
        'no_comments_message' => '',
        'top_panel' => array(
            'main_link' => '',
            'main_title' => '',
            'logo_url' => '',
            'links' => array()
        ),
        'bottom_panel' => array(
            'bottom_message' => '',
            'sections' => array()
        ),
    ));

    $comments_title = $settings['comments_title'];
    $no_comments_message = $settings['no_comments_message'];
    $reply_button_label = $settings['reply_button_label'] ?? '';
    $leave_comment_label = $settings['leave_comment_label'] ?? '';


    $top_panel = $settings['top_panel'];
    $bottom_panel = $settings['bottom_panel'];

    $main_link = $top_panel['main_link'];
    
    $site_name = $top_panel['main_title'];

    $logo_url = $top_panel['logo_url'];


    $bottom_message = $bottom_panel['bottom_message'];


    $should_show_top_panel = !empty($site_name) || !empty($main_link) || !empty($logo_url) || !empty($top_panel['links']);
    $should_show_side_panel = stwbpb_has_comment_section($post);
    $should_show_bottom_panel = !empty($bottom_message) || !empty($bottom_panel['sections']);

    $should_show_panels = $should_show_top_panel || $should_show_side_panel || $should_show_bottom_panel;

    $side_panel_left = (bool) ($settings['side_panel_on_the_left'] ?? false);
    $side_panel_attribute = $side_panel_left ? ' side="left"' : '';
    
    if(!$should_show_panels){
        return '';
    }
    
    ob_start();

?>
<panels>
<?php if($should_show_top_panel){ ?>
<top>
<?php 
if(!empty($logo_url)){
    echo '<logo src="' . esc_url($logo_url) . '"';
    if(!empty($main_link)){
        echo ' href="' . esc_url($main_link) . '"';
    }
    echo '/>';
}
if(!empty($site_name)){
    echo '<site-name';
    if(!empty($main_link) && empty($logo_url)){ 
        echo ' href="' . esc_url($main_link) . '"';
    }
    echo '>' . esc_attr($site_name) . '</site-name>';
}

?>
<?php
if (!empty($top_panel['links'])) {
    foreach ($top_panel['links'] as $index => $link) {
        $link_to_use = $link['url'];

        echo '<a href="' . esc_url($link_to_use) . '">' . esc_html($link['text']) . '</a>' . PHP_EOL; 
    }
}
?>
</top>
<?php
}
?>
<?php if ($should_show_side_panel): ?>
<?php
$commenting_open = $post->comment_status === 'open';
$comments_attrs = '';
$comments_attrs .= !empty($comments_title)      ? ' title="'              . esc_attr($comments_title)      . '"' : '';
$comments_attrs .= !empty($no_comments_message) ? ' empty="'              . esc_attr($no_comments_message) . '"' : '';
if ($commenting_open) {
    $leave_comment_url = home_url("/sw-comment-form/?post={$post->ID}");
    $comments_attrs .= ' leave-comment-url="'                             . esc_url($leave_comment_url)     . '"';
    $comments_attrs .= !empty($reply_button_label)  ? ' reply-label="'         . esc_attr($reply_button_label)  . '"' : '';
    $comments_attrs .= !empty($leave_comment_label) ? ' leave-comment-label="' . esc_attr($leave_comment_label) . '"' : '';
}
?>
<side<?php if(!empty($side_panel_left)){ echo ' left="true"'; } ?>><?php echo '<comments' . $comments_attrs . '>' . esc_url($comments_link) . '</comments>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $comments_attrs is built entirely from esc_attr/esc_url calls above. ?></side>
<?php endif; ?>
<?php if($should_show_bottom_panel){ ?>
<bottom>
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
        $link_to_use = $link['url'];
        
        echo '<a href="' . esc_url($link_to_use) . '">' . esc_html($link['text']) . '</a>' . PHP_EOL; 
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
</bottom>
<?php
}
?>
</panels><?php

    $output = ob_get_clean();

    return $output;

}


function stwbpb_get_seo_panel_data($panels_xml) {
    if (empty($panels_xml)) return null;

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($panels_xml);
    libxml_clear_errors();
    if ($xml === false) return null;

    $data = [
        'logo_href'       => null,
        'logo_src'        => null,
        'site_name'       => null,
        'site_name_href'  => null,
        'top_links'       => [],
        'bottom_sections' => [],
        'bottom_message'  => null,
    ];

    if (isset($xml->top)) {
        $top = $xml->top;
        if (isset($top->logo)) {
            $logo = $top->logo;
            $data['logo_src']  = isset($logo['src'])  ? (string) $logo['src']  : null;
            $data['logo_href'] = isset($logo['href']) ? (string) $logo['href'] : null;
        }
        if (isset($top->{'site-name'})) {
            $sn = $top->{'site-name'};
            $data['site_name']      = (string) $sn;
            $data['site_name_href'] = isset($sn['href']) ? (string) $sn['href'] : null;
        }
        foreach ($top->a as $a) {
            $href = isset($a['href']) ? (string) $a['href'] : '';
            $text = (string) $a;
            if ($href !== '') {
                $data['top_links'][] = ['href' => $href, 'text' => $text];
            }
        }
    }

    if (isset($xml->bottom)) {
        $bottom = $xml->bottom;
        foreach ($bottom->section as $section) {
            $section_title = isset($section['title']) ? (string) $section['title'] : '';
            $links = [];
            foreach ($section->a as $a) {
                $href = isset($a['href']) ? (string) $a['href'] : '';
                $text = (string) $a;
                if ($href !== '') {
                    $links[] = ['href' => $href, 'text' => $text];
                }
            }
            $data['bottom_sections'][] = ['title' => $section_title, 'links' => $links];
        }
        if (isset($bottom->{'bottom-message'})) {
            $data['bottom_message'] = (string) $bottom->{'bottom-message'};
        }
    }

    return $data;
}


function stwbpb_has_comment_section($post) {
    if (!$post || !function_exists('comments_template')) return false;
    $commenting_open = $post->comment_status === 'open';
    $has_approved_comments = (int) get_comments_number($post->ID) > 0;
    return $commenting_open || $has_approved_comments;
}