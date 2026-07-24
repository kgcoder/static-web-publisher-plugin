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

    $show_post_nav = !empty($settings['show_post_nav']);
    $prev_post = null;
    $next_post = null;
    $should_show_post_nav = false;
    if ($show_post_nav && $post->post_type === 'post') {
        $prev_post = get_adjacent_post(false, '', true);
        $next_post = get_adjacent_post(false, '', false);
        $should_show_post_nav = !empty($prev_post) || !empty($next_post);
    }

    $effective_sidebar_id = stwbpb_get_effective_sidebar($post);
    if ($effective_sidebar_id !== 'none') {
        $sidebar_variant = stwbpb_sidebar_variants_get_by_id($effective_sidebar_id);
        $sidebar_sections = ($sidebar_variant && !empty($sidebar_variant['sections'])) ? $sidebar_variant['sections'] : array();
    } else {
        $sidebar_sections = array();
    }
    $should_show_sidebar = !empty($sidebar_sections);

    $should_show_top_panel = !empty($site_name) || !empty($main_link) || !empty($logo_url) || !empty($top_panel['links']);
    $should_show_comments_panel = stwbpb_has_comment_section($post);
    $should_show_bottom_panel = !empty($bottom_message) || !empty($bottom_panel['sections']);

    $should_show_panels = $should_show_top_panel || $should_show_post_nav || $should_show_sidebar || $should_show_comments_panel || $should_show_bottom_panel;

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
    echo '>' . esc_html(stwbpb_decode_entities($site_name)) . '</site-name>';
}

?>
<?php
if (!empty($top_panel['links'])) {
    foreach ($top_panel['links'] as $index => $link) {
        $link_to_use = $link['url'];

        echo '<a href="' . esc_url($link_to_use) . '">' . esc_html(stwbpb_decode_entities($link['text'])) . '</a>' . PHP_EOL; 
    }
}
?>
</top>
<?php
}
?>
<?php if ($should_show_post_nav): ?>
<post-nav>
<?php if (!empty($prev_post)): ?><prev href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>"><?php echo esc_html(stwbpb_decode_entities($prev_post->post_title)); ?></prev>
<?php endif; if (!empty($next_post)): ?><next href="<?php echo esc_url(get_permalink($next_post->ID)); ?>"><?php echo esc_html(stwbpb_decode_entities($next_post->post_title)); ?></next>
<?php endif; ?>
</post-nav>
<?php endif; ?>
<?php if ($should_show_sidebar): ?>
<sidebar<?php if (!empty($sidebar_variant['side_left'])) echo ' side="left"'; ?>>
<?php
foreach ($sidebar_sections as $sec) {
    $sec_type = $sec['type'] ?? '';
    if ($sec_type === 'search') {
        $search_action = $sec['action'] ?? '';
        if (!empty($search_action)) {
            echo '<search action="' . esc_attr(stwbpb_decode_entities($search_action)) . '"';
            if (!empty($sec['placeholder'])) echo ' placeholder="' . esc_attr(stwbpb_decode_entities($sec['placeholder'])) . '"';
            if (!empty($sec['target']))      echo ' target="' . esc_attr(stwbpb_decode_entities($sec['target'])) . '"';
            echo '/>' . PHP_EOL;
        }
    } elseif ($sec_type === 'links') {
        if (!empty($sec['links'])) {
            echo '<links';
            if (!empty($sec['title'])) echo ' title="' . esc_attr(stwbpb_decode_entities($sec['title'])) . '"';
            echo '>' . PHP_EOL;
            foreach ($sec['links'] as $lnk) {
                if (!empty($lnk['url'])) {
                    echo '<a href="' . esc_url($lnk['url']) . '"';
                    if (!empty($lnk['target'])) echo ' target="' . esc_attr(stwbpb_decode_entities($lnk['target'])) . '"';
                    if (!empty($lnk['rel']))    echo ' rel="' . esc_attr(stwbpb_decode_entities($lnk['rel'])) . '"';
                    echo '>' . esc_html(stwbpb_decode_entities($lnk['text'])) . '</a>' . PHP_EOL;
                }
            }
            echo '</links>' . PHP_EOL;
        }
    } elseif ($sec_type === 'recent-posts') {
        $recent_posts = get_posts(array('numberposts' => (int) ($sec['max'] ?? 5), 'post_type' => 'post', 'post_status' => 'publish'));
        if (!empty($recent_posts)) {
            echo '<links';
            if (!empty($sec['title'])) echo ' title="' . esc_attr(stwbpb_decode_entities($sec['title'])) . '"';
            echo '>' . PHP_EOL;
            foreach ($recent_posts as $rp) {
                echo '<a href="' . esc_url(get_permalink($rp->ID)) . '">' . esc_html(stwbpb_decode_entities($rp->post_title)) . '</a>' . PHP_EOL;
            }
            echo '</links>' . PHP_EOL;
        }
    } elseif ($sec_type === 'recent-comments') {
        $recent_comments = get_comments(array('number' => (int) ($sec['max'] ?? 5), 'status' => 'approve', 'type' => 'comment'));
        if (!empty($recent_comments)) {
            echo '<recent-comments';
            if (!empty($sec['title']))  echo ' title="' . esc_attr(stwbpb_decode_entities($sec['title'])) . '"';
            if (!empty($sec['format'])) echo ' format="' . esc_attr(stwbpb_decode_entities($sec['format'])) . '"';
            echo '>' . PHP_EOL;
            $include_excerpt = !empty($sec['include_excerpt']);
            foreach ($recent_comments as $rc) {
                $rc_post = get_post($rc->comment_post_ID);
                if (!$rc_post) continue;
                echo '<comment post-href="' . esc_url(get_permalink($rc_post->ID)) . '"';
                echo ' post-title="' . esc_attr(stwbpb_decode_entities($rc_post->post_title)) . '"';
                echo ' author="' . esc_attr(stwbpb_decode_entities($rc->comment_author)) . '"';
                if ($include_excerpt && !empty($rc->comment_content)) {
                    echo ' excerpt="' . esc_attr(stwbpb_decode_entities(wp_trim_words($rc->comment_content, 15, '…'))) . '"';
                }
                echo '/>' . PHP_EOL;
            }
            echo '</recent-comments>' . PHP_EOL;
        }
    }
}
?>
</sidebar>
<?php endif; ?>
<?php if ($should_show_comments_panel): ?>
<?php
$commenting_open = $post->comment_status === 'open';
$comments_attrs = '';
$comments_attrs .= !empty($comments_title)      ? ' title="'              . esc_attr(stwbpb_decode_entities($comments_title))      . '"' : '';
$comments_attrs .= !empty($no_comments_message) ? ' empty="'              . esc_attr(stwbpb_decode_entities($no_comments_message)) . '"' : '';
if ($commenting_open) {
    $leave_comment_url = home_url("/sw-comment-form/?post={$post->ID}");
    $comments_attrs .= ' leave-comment-url="'                             . esc_url($leave_comment_url)     . '"';
    $comments_attrs .= !empty($reply_button_label)  ? ' reply-label="'         . esc_attr(stwbpb_decode_entities($reply_button_label))  . '"' : '';
    $comments_attrs .= !empty($leave_comment_label) ? ' leave-comment-label="' . esc_attr(stwbpb_decode_entities($leave_comment_label)) . '"' : '';
}
?>
<?php echo '<comments' . $comments_attrs . '>' . esc_url($comments_link) . '</comments>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $comments_attrs is built entirely from esc_attr/esc_url calls above. ?>
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
    echo ' title="' . esc_attr(stwbpb_decode_entities($section_title)) . '"';
}
?>>
<?php
if (!empty($section['links'])) {
    foreach ($section['links'] as $index => $link) {
        $link_to_use = $link['url'];
        
        echo '<a href="' . esc_url($link_to_use) . '">' . esc_html(stwbpb_decode_entities($link['text'])) . '</a>' . PHP_EOL; 
    }
}
?>
</section>
<?php
    }
}
?>
<?php if(!empty($bottom_message)){
    echo '<bottom-message>' . esc_html(stwbpb_decode_entities($bottom_message)) . '</bottom-message>';
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
        'post_nav'        => null,
        'sidebar'         => null,
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

    if (isset($xml->{'post-nav'})) {
        $pn = $xml->{'post-nav'};
        $post_nav_data = [];
        if (isset($pn->prev)) {
            $post_nav_data['prev'] = [
                'href'  => isset($pn->prev['href']) ? (string) $pn->prev['href'] : '',
                'title' => (string) $pn->prev,
            ];
        }
        if (isset($pn->next)) {
            $post_nav_data['next'] = [
                'href'  => isset($pn->next['href']) ? (string) $pn->next['href'] : '',
                'title' => (string) $pn->next,
            ];
        }
        if (!empty($post_nav_data)) {
            $data['post_nav'] = $post_nav_data;
        }
    }

    if (isset($xml->sidebar)) {
        $sb = $xml->sidebar;
        $sb_side = isset($sb['side']) ? (string) $sb['side'] : 'right';
        $sb_items = [];
        foreach ($sb->children() as $sb_child_name => $sb_child) {
            if ($sb_child_name === 'search') {
                $sb_items[] = [
                    'type'        => 'search',
                    'action'      => isset($sb_child['action'])      ? (string) $sb_child['action']      : '',
                    'placeholder' => isset($sb_child['placeholder']) ? (string) $sb_child['placeholder'] : '',
                ];
            } elseif ($sb_child_name === 'links') {
                $sb_links = [];
                foreach ($sb_child->a as $a) {
                    $sb_links[] = [
                        'href' => isset($a['href']) ? (string) $a['href'] : '',
                        'text' => (string) $a,
                    ];
                }
                $sb_items[] = [
                    'type'  => 'links',
                    'title' => isset($sb_child['title']) ? (string) $sb_child['title'] : '',
                    'links' => $sb_links,
                ];
            } elseif ($sb_child_name === 'recent-comments') {
                $sb_comments = [];
                foreach ($sb_child->comment as $c) {
                    $sb_comments[] = [
                        'post-href'  => isset($c['post-href'])  ? (string) $c['post-href']  : '',
                        'post-title' => isset($c['post-title']) ? (string) $c['post-title'] : '',
                        'author'     => isset($c['author'])     ? (string) $c['author']     : '',
                        'excerpt'    => isset($c['excerpt'])    ? (string) $c['excerpt']    : '',
                    ];
                }
                $sb_items[] = [
                    'type'     => 'recent-comments',
                    'title'    => isset($sb_child['title']) ? (string) $sb_child['title'] : '',
                    'comments' => $sb_comments,
                ];
            }
        }
        if (!empty($sb_items)) {
            $data['sidebar'] = ['side' => $sb_side, 'items' => $sb_items];
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