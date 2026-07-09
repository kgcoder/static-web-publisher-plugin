<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function stwbpb_custom_post_endpoints_add_meta_box() {
    add_meta_box(
        'custom_post_endpoints_meta_box',
        'Static Web Publisher Settings',
        'stwbpb_custom_post_endpoints_meta_box_callback',
        array('post', 'page'), // Enable for posts and pages
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'stwbpb_custom_post_endpoints_add_meta_box');

function stwbpb_custom_post_endpoints_meta_box_callback($post) {
    $connections_info        = get_post_meta($post->ID, '_static_web_connections_info', true);
    $mode_value              = get_post_meta($post->ID, '_hdoc_display_mode', true) ?: 'default';
    $author_display          = get_post_meta($post->ID, '_hdoc_author_name_display', true) ?: 'default';
    $date_display            = get_post_meta($post->ID, '_hdoc_publish_date_display', true) ?: 'default';
    $doc_type                = get_post_meta($post->ID, '_doc_type', true) ?: 'HDOC';
    $condoc_description      = get_post_meta($post->ID, '_condoc_description', true);
    $condoc_main_url         = get_post_meta($post->ID, '_condoc_main_url', true);
    $cdoc_svg                = get_post_meta($post->ID, '_cdoc_svg', true);
    $sidebar_value           = get_post_meta($post->ID, '_stwbpb_sidebar', true) ?: 'default';
    $republishing_policy     = get_post_meta($post->ID, '_republishing_policy', true) ?: 'default';
    $sidebar_variants        = stwbpb_sidebar_variants_get_all();

    wp_nonce_field('custom_post_endpoints_meta_box_nonce', 'custom_post_endpoints_nonce');
    ?>
    <label for="doc_type"><strong>Document type:</strong></label><br>
    <select name="doc_type" id="doc_type" style="width:100%;margin-top:4px;">
        <option value="HDOC"   <?php selected($doc_type, 'HDOC'); ?>>HDOC</option>
        <option value="CDOC"   <?php selected($doc_type, 'CDOC'); ?>>CDOC</option>
        <option value="CONDOC" <?php selected($doc_type, 'CONDOC'); ?>>CONDOC</option>
    </select>
    <br><br>
    <div id="cdoc_fields">
        <label for="cdoc_svg"><strong>SVG for CDOC:</strong></label>
        <textarea name="cdoc_svg" id="cdoc_svg" rows="4" style="width:100%;margin-top:4px;"><?php echo esc_textarea($cdoc_svg); ?></textarea>
        <br><br>
    </div>
    <div id="condoc_fields">
        <label for="condoc_description"><strong>CONDOC description:</strong></label><br>
        <input type="text" name="condoc_description" id="condoc_description" value="<?php echo esc_attr($condoc_description); ?>" style="width:100%;margin-top:4px;">
        <br><br>
        <label for="condoc_main_url"><strong>CONDOC main URL:</strong></label><br>
        <input type="text" name="condoc_main_url" id="condoc_main_url" value="<?php echo esc_attr($condoc_main_url); ?>" style="width:100%;margin-top:4px;">
        <br><br>
    </div>
    <label for="hdoc_display_mode"><strong>Display mode:</strong></label><br>
    <select name="hdoc_display_mode" id="hdoc_display_mode" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($mode_value, 'default'); ?>>Default (use global setting)</option>
        <option value="embedded_hdoc_forced" <?php selected($mode_value, 'embedded_hdoc_forced'); ?>>Embedded HDOC (forced)</option>
        <option value="embedded_hdoc" <?php selected($mode_value, 'embedded_hdoc'); ?>>Embedded HDOC</option>
        <option value="doc_in_reader" <?php selected($mode_value, 'doc_in_reader'); ?>>Doc inside the Reader</option>
        <option value="standalone_doc" <?php selected($mode_value, 'standalone_doc'); ?>>Standalone doc</option>
    </select>
    <br><br>
    <label for="hdoc_author_name_display"><strong>Author's name:</strong></label><br>
    <select name="hdoc_author_name_display" id="hdoc_author_name_display" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($author_display, 'default'); ?>>Default (use global setting)</option>
        <option value="show"    <?php selected($author_display, 'show'); ?>>Show</option>
        <option value="hide"    <?php selected($author_display, 'hide'); ?>>Hide</option>
    </select>
    <br><br>
    <label for="hdoc_publish_date_display"><strong>Publish date:</strong></label><br>
    <select name="hdoc_publish_date_display" id="hdoc_publish_date_display" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($date_display, 'default'); ?>>Default (use global setting)</option>
        <option value="show"    <?php selected($date_display, 'show'); ?>>Show</option>
        <option value="hide"    <?php selected($date_display, 'hide'); ?>>Hide</option>
    </select>
    <br><br>
    <label for="republishing_policy"><strong>Republishing policy:</strong></label><br>
    <select name="republishing_policy" id="republishing_policy" style="width:100%;margin-top:4px;">
        <option value="default"        <?php selected($republishing_policy, 'default'); ?>>Default (use global setting)</option>
        <option value="implicit_allow" <?php selected($republishing_policy, 'implicit_allow'); ?>>Implicitly allow (no tag)</option>
        <option value="explicit_allow" <?php selected($republishing_policy, 'explicit_allow'); ?>>Explicitly allow</option>
        <option value="prohibit"       <?php selected($republishing_policy, 'prohibit'); ?>>Prohibit (do-not-republish)</option>
    </select>
    <br><br>
    <label for="stwbpb_sidebar_override"><strong>Sidebar:</strong></label><br>
    <select name="stwbpb_sidebar_override" id="stwbpb_sidebar_override" style="width:100%;margin-top:4px;">
        <option value="default" <?php selected($sidebar_value, 'default'); ?>>Default (use global setting)</option>
        <option value="none" <?php selected($sidebar_value, 'none'); ?>>None</option>
        <?php foreach ($sidebar_variants as $sv): ?>
            <option value="<?php echo esc_attr($sv['id']); ?>" <?php selected($sidebar_value, $sv['id']); ?>><?php echo esc_html($sv['title']); ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <label for="static_web_connections_info"><strong>Connections Info:</strong></label>
    <textarea name="static_web_connections_info" id="static_web_connections_info" rows="3" style="width:100%;"><?php echo esc_textarea($connections_info); ?></textarea>
    <p class="description">Add connections info</p>
    <script>
    (function() {
        const sel = document.getElementById('doc_type');
        function toggleFields() {
            const v = sel.value;
            document.getElementById('condoc_fields').style.display = (v === 'CONDOC') ? '' : 'none';
            document.getElementById('cdoc_fields').style.display   = (v === 'CDOC')   ? '' : 'none';
        }
        sel.addEventListener('change', toggleFields);
        toggleFields();
    })();
    </script>
    <?php
}

function stwbpb_custom_post_endpoints_save_meta_box($post_id) {
    if (!isset($_POST['custom_post_endpoints_nonce'])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST['custom_post_endpoints_nonce']));

    if (!wp_verify_nonce($nonce, 'custom_post_endpoints_meta_box_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $allowed_doc_types = array('HDOC', 'CDOC', 'CONDOC');
    $doc_type = isset($_POST['doc_type']) && in_array($_POST['doc_type'], $allowed_doc_types, true)
        ? sanitize_text_field(wp_unslash($_POST['doc_type']))
        : 'HDOC';
    update_post_meta($post_id, '_doc_type', $doc_type);

    $condoc_description = isset($_POST['condoc_description'])
        ? sanitize_text_field(wp_unslash($_POST['condoc_description']))
        : '';
    update_post_meta($post_id, '_condoc_description', $condoc_description);

    $condoc_main_url = isset($_POST['condoc_main_url'])
        ? esc_url_raw(wp_unslash($_POST['condoc_main_url']))
        : '';
    update_post_meta($post_id, '_condoc_main_url', $condoc_main_url);

    $svg_allowed_tags = array(
        'svg'      => array('xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'viewBox' => true, 'class' => true, 'id' => true, 'style' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'transform' => true, 'preserveaspectratio' => true, 'preserveAspectRatio' => true),
        'g'        => array('id' => true, 'class' => true, 'transform' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'style' => true),
        'path'     => array('d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true, 'transform' => true, 'opacity' => true),
        'circle'   => array('cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true, 'opacity' => true),
        'rect'     => array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true, 'transform' => true, 'opacity' => true),
        'ellipse'  => array('cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true, 'opacity' => true),
        'line'     => array('x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true),
        'polyline' => array('points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true),
        'polygon'  => array('points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'id' => true, 'class' => true, 'style' => true),
        'text'     => array('x' => true, 'y' => true, 'fill' => true, 'font-size' => true, 'font-family' => true, 'text-anchor' => true, 'id' => true, 'class' => true, 'style' => true, 'transform' => true),
        'tspan'    => array('x' => true, 'y' => true, 'dx' => true, 'dy' => true, 'fill' => true, 'font-size' => true, 'id' => true, 'class' => true, 'style' => true),
        'defs'     => array('id' => true),
        'use'      => array('href' => true, 'xlink:href' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'id' => true, 'class' => true, 'style' => true),
        'symbol'   => array('id' => true, 'viewbox' => true, 'viewBox' => true, 'width' => true, 'height' => true),
        'title'    => array(),
        'desc'     => array(),
        'a'        => array('href' => true, 'xlink:href' => true, 'target' => true, 'id' => true, 'class' => true, 'style' => true, 'transform' => true),
        'image'    => array('href' => true, 'xlink:href' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'id' => true, 'class' => true, 'style' => true, 'transform' => true, 'preserveaspectratio' => true, 'preserveAspectRatio' => true),
    );
    $cdoc_svg = isset($_POST['cdoc_svg'])
        ? wp_kses(wp_unslash($_POST['cdoc_svg']), $svg_allowed_tags)
        : '';
    update_post_meta($post_id, '_cdoc_svg', $cdoc_svg);

    $allowed_modes = array('default', 'embedded_hdoc_forced', 'embedded_hdoc', 'doc_in_reader', 'standalone_doc');
    $mode = isset($_POST['hdoc_display_mode']) && in_array($_POST['hdoc_display_mode'], $allowed_modes, true)
        ? sanitize_text_field(wp_unslash($_POST['hdoc_display_mode']))
        : 'default';
    update_post_meta($post_id, '_hdoc_display_mode', $mode);

    $allowed_vis = array('default', 'show', 'hide');
    $author_vis = isset($_POST['hdoc_author_name_display']) && in_array($_POST['hdoc_author_name_display'], $allowed_vis, true)
        ? sanitize_text_field(wp_unslash($_POST['hdoc_author_name_display']))
        : 'default';
    update_post_meta($post_id, '_hdoc_author_name_display', $author_vis);

    $date_vis = isset($_POST['hdoc_publish_date_display']) && in_array($_POST['hdoc_publish_date_display'], $allowed_vis, true)
        ? sanitize_text_field(wp_unslash($_POST['hdoc_publish_date_display']))
        : 'default';
    update_post_meta($post_id, '_hdoc_publish_date_display', $date_vis);

    $allowed_sidebar_values = array_merge(array('default', 'none'), array_column(stwbpb_sidebar_variants_get_all(), 'id'));
    $sidebar_override = isset($_POST['stwbpb_sidebar_override']) && in_array($_POST['stwbpb_sidebar_override'], $allowed_sidebar_values, true)
        ? sanitize_text_field(wp_unslash($_POST['stwbpb_sidebar_override']))
        : 'default';
    update_post_meta($post_id, '_stwbpb_sidebar', $sidebar_override);

    $allowed_rep_policies = array('default', 'implicit_allow', 'explicit_allow', 'prohibit');
    $rep_policy = isset($_POST['republishing_policy']) && in_array($_POST['republishing_policy'], $allowed_rep_policies, true)
        ? sanitize_text_field(wp_unslash($_POST['republishing_policy']))
        : 'default';
    update_post_meta($post_id, '_republishing_policy', $rep_policy);

    $allowed_tags = array(
        'doc'  => array('url' => true, 'title' => true, 'hash' => true),
    );

    if (isset($_POST['static_web_connections_info'])) {

        $connections_info = wp_kses(wp_unslash($_POST['static_web_connections_info']), $allowed_tags);

        update_post_meta($post_id, '_static_web_connections_info', $connections_info);
    }

}
add_action('save_post', 'stwbpb_custom_post_endpoints_save_meta_box');



function stwbpb_get_effective_display_mode($post) {
    $meta_mode = get_post_meta($post->ID, '_hdoc_display_mode', true);
    if (!empty($meta_mode) && $meta_mode !== 'default') {
        return $meta_mode;
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_mode' : 'post_mode';
    return isset($settings[$key]) ? $settings[$key] : 'embedded_hdoc';
}

function stwbpb_get_effective_author_display($post) {
    $meta = get_post_meta($post->ID, '_hdoc_author_name_display', true);
    if (!empty($meta) && $meta !== 'default') {
        return $meta;
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_author_name' : 'post_author_name';
    return isset($settings[$key]) ? $settings[$key] : 'show';
}

function stwbpb_get_effective_date_display($post) {
    $meta = get_post_meta($post->ID, '_hdoc_publish_date_display', true);
    if (!empty($meta) && $meta !== 'default') {
        return $meta;
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_publish_date' : 'post_publish_date';
    return isset($settings[$key]) ? $settings[$key] : 'show';
}

function stwbpb_get_effective_republishing_policy($post) {
    $meta = get_post_meta($post->ID, '_republishing_policy', true);
    if (!empty($meta) && $meta !== 'default') {
        return $meta;
    }
    $settings = get_option('stwbpb_settings', array());
    return isset($settings['republishing_policy']) ? $settings['republishing_policy'] : 'implicit_allow';
}

function stwbpb_get_effective_doc_type($post) {
    $t = get_post_meta($post->ID, '_doc_type', true);
    return in_array($t, array('HDOC', 'CDOC', 'CONDOC'), true) ? $t : 'HDOC';
}

function stwbpb_get_doc_effective_display_mode($post) {
    $mode = stwbpb_get_effective_display_mode($post);
    $type = stwbpb_get_effective_doc_type($post);
    if ($type === 'CDOC' || $type === 'CONDOC') {
        return ($mode === 'standalone_doc') ? 'standalone_doc' : 'doc_in_reader';
    }
    return $mode;
}

function stwbpb_get_effective_sidebar($post) {
    $meta = get_post_meta($post->ID, '_stwbpb_sidebar', true);
    if (!empty($meta) && $meta !== 'default') {
        return $meta; // 'none' or a variant ID
    }
    $settings = get_option('stwbpb_settings', array());
    $key = ($post->post_type === 'page') ? 'page_sidebar' : 'post_sidebar';
    return isset($settings[$key]) ? $settings[$key] : 'none';
}

/**
 * Confirms $post is genuinely the page being rendered at the current URL,
 * rather than a stale/default main-query object (e.g. the static front page)
 * left behind because another plugin short-circuited template_redirect with
 * unrecognized query args. Guards against injecting HDOC output/assets into
 * unrelated pages that merely happen to share the main query's $post global.
 */
function stwbpb_request_matches_post($post) {
    if (empty($post)) {
        return false;
    }

    // Direct ID/slug query access (?p=123, ?page_id=123, ?attachment_id=123,
    // ?name=slug, ?pagename=slug) is always valid in WordPress regardless of
    // permalink structure and won't match the pretty permalink path below.
    foreach (array('p', 'page_id', 'attachment_id') as $id_var) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only comparison against the current query, not form processing.
        if (isset($_GET[$id_var]) && (int) $_GET[$id_var] === (int) $post->ID) {
            return true;
        }
    }
    foreach (array('name', 'pagename') as $slug_var) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only comparison against the current query, not form processing.
        if (isset($_GET[$slug_var]) && sanitize_title(wp_unslash($_GET[$slug_var])) === $post->post_name) {
            return true;
        }
    }

    $permalink = get_permalink($post);
    if (!$permalink) {
        return false;
    }

    $request_path   = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH) : '';
    $permalink_path = wp_parse_url($permalink, PHP_URL_PATH);

    return untrailingslashit((string) $request_path) === untrailingslashit((string) $permalink_path);
}

?>