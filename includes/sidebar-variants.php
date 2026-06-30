<?php

if (!defined('ABSPATH')) {
    exit;
}

// --- Data helpers ---

function stwbpb_sidebar_variants_get_all() {
    return get_option('stwbpb_sidebar_variants', array());
}

function stwbpb_sidebar_variants_save_all($variants) {
    update_option('stwbpb_sidebar_variants', array_values($variants));
}

function stwbpb_sidebar_variants_get_by_id($id) {
    foreach (stwbpb_sidebar_variants_get_all() as $variant) {
        if (isset($variant['id']) && $variant['id'] === $id) {
            return $variant;
        }
    }
    return null;
}

function stwbpb_sanitize_sidebar_sections($raw) {
    $allowed_targets = array('_self', '_blank');
    $sections = array();
    if (!is_array($raw)) {
        return $sections;
    }
    foreach ($raw as $sec) {
        if (!is_array($sec)) continue;
        $type = isset($sec['type']) ? $sec['type'] : '';
        if ($type === 'search') {
            $sections[] = array(
                'type'        => 'search',
                'action'      => isset($sec['action'])      ? sanitize_text_field($sec['action'])      : '',
                'placeholder' => isset($sec['placeholder']) ? sanitize_text_field($sec['placeholder']) : '',
                'target'      => isset($sec['target']) && in_array($sec['target'], $allowed_targets, true) ? $sec['target'] : '_self',
            );
        } elseif ($type === 'recent-comments') {
            $sections[] = array(
                'type'            => 'recent-comments',
                'title'           => isset($sec['title'])   ? sanitize_text_field($sec['title'])  : '',
                'max'             => isset($sec['max'])      ? max(1, absint($sec['max']))         : 5,
                'format'          => isset($sec['format'])   ? sanitize_text_field($sec['format']) : '',
                'include_excerpt' => boolval(isset($sec['include_excerpt']) ? $sec['include_excerpt'] : false),
            );
        } elseif ($type === 'links') {
            $sanitized_links = array();
            if (isset($sec['links']) && is_array($sec['links'])) {
                foreach ($sec['links'] as $lnk) {
                    if (!is_array($lnk)) continue;
                    $lnk_target = isset($lnk['target']) && in_array($lnk['target'], $allowed_targets, true) ? $lnk['target'] : '';
                    $sanitized_links[] = array(
                        'text'   => isset($lnk['text']) ? sanitize_text_field($lnk['text']) : '',
                        'url'    => isset($lnk['url'])  ? esc_url_raw($lnk['url'])          : '',
                        'target' => $lnk_target,
                        'rel'    => isset($lnk['rel'])  ? sanitize_text_field($lnk['rel'])  : '',
                    );
                }
            }
            $sections[] = array(
                'type'  => 'links',
                'title' => isset($sec['title']) ? sanitize_text_field($sec['title']) : '',
                'links' => $sanitized_links,
            );
        } elseif ($type === 'recent-posts') {
            $sections[] = array(
                'type'  => 'recent-posts',
                'title' => isset($sec['title']) ? sanitize_text_field($sec['title']) : '',
                'max'   => isset($sec['max'])   ? max(1, absint($sec['max']))        : 5,
            );
        }
    }
    return $sections;
}

// --- Admin page registration ---

function stwbpb_register_sidebar_variant_pages() {
    add_submenu_page(
        'static_web_publisher_settings',
        'Sidebar Variants',
        'Sidebar Variants',
        'manage_options',
        'stwbpb-sidebar-variants',
        'stwbpb_sidebar_variants_list_page'
    );
    add_submenu_page(
        null,
        'Edit Sidebar Variant',
        '',
        'manage_options',
        'stwbpb-sidebar-variant-edit',
        'stwbpb_sidebar_variant_edit_page'
    );
}
add_action('admin_menu', 'stwbpb_register_sidebar_variant_pages');

// --- Form handler ---

function stwbpb_handle_sidebar_variant_admin() {
    if (!isset($_POST['action'])) return;

    $action = sanitize_key($_POST['action']);
    if (!in_array($action, array('stwbpb_create_sidebar_variant', 'stwbpb_save_sidebar_variant', 'stwbpb_delete_sidebar_variant'), true)) {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to perform this action.'));
    }

    check_admin_referer('stwbpb_sidebar_variant_action');

    $list_url = admin_url('admin.php?page=stwbpb-sidebar-variants');

    if ($action === 'stwbpb_create_sidebar_variant') {
        $id = 'sv_' . time();
        $variants = stwbpb_sidebar_variants_get_all();
        $variants[] = array('id' => $id, 'title' => '', 'sections' => array());
        stwbpb_sidebar_variants_save_all($variants);
        wp_redirect(admin_url('admin.php?page=stwbpb-sidebar-variant-edit&variant_id=' . urlencode($id)));
        exit;
    }

    if ($action === 'stwbpb_delete_sidebar_variant') {
        $id = isset($_POST['variant_id']) ? sanitize_text_field($_POST['variant_id']) : '';
        $variants = stwbpb_sidebar_variants_get_all();
        $variants = array_filter($variants, function ($v) use ($id) {
            return isset($v['id']) && $v['id'] !== $id;
        });
        stwbpb_sidebar_variants_save_all($variants);
        wp_redirect($list_url . '&swp_notice=deleted');
        exit;
    }

    if ($action === 'stwbpb_save_sidebar_variant') {
        $raw       = isset($_POST['stwbpb_variant']) && is_array($_POST['stwbpb_variant']) ? $_POST['stwbpb_variant'] : array();
        $id        = isset($raw['id']) ? sanitize_text_field($raw['id']) : '';
        $title     = isset($raw['title']) ? sanitize_text_field($raw['title']) : '';
        $side_left = !empty($raw['side_left']);
        $sections  = stwbpb_sanitize_sidebar_sections(isset($raw['sections']) ? $raw['sections'] : array());

        if ($title === '') {
            $edit_url = admin_url('admin.php?page=stwbpb-sidebar-variant-edit&variant_id=' . urlencode($id) . '&swp_notice=empty_title');
            wp_redirect($edit_url);
            exit;
        }

        $variants = stwbpb_sidebar_variants_get_all();

        // Check title uniqueness (excluding current variant)
        foreach ($variants as $v) {
            if (isset($v['id']) && $v['id'] !== $id && isset($v['title']) && $v['title'] === $title) {
                $edit_url = admin_url('admin.php?page=stwbpb-sidebar-variant-edit&variant_id=' . urlencode($id) . '&swp_notice=duplicate_title');
                wp_redirect($edit_url);
                exit;
            }
        }

        $updated = false;
        foreach ($variants as &$v) {
            if (isset($v['id']) && $v['id'] === $id) {
                $v['title']     = $title;
                $v['side_left'] = $side_left;
                $v['sections']  = $sections;
                $updated = true;
                break;
            }
        }
        unset($v);

        if (!$updated) {
            // Variant not found — add it (edge case: saving a brand-new variant whose create step was skipped)
            $variants[] = array('id' => $id, 'title' => $title, 'side_left' => $side_left, 'sections' => $sections);
        }

        stwbpb_sidebar_variants_save_all($variants);
        wp_redirect($list_url . '&swp_notice=saved');
        exit;
    }
}
add_action('admin_init', 'stwbpb_handle_sidebar_variant_admin');

// --- List page ---

function stwbpb_sidebar_variants_list_page() {
    if (!current_user_can('manage_options')) return;

    $variants = stwbpb_sidebar_variants_get_all();
    $notice   = isset($_GET['swp_notice']) ? sanitize_key($_GET['swp_notice']) : '';
    ?>
    <div class="wrap">
        <h1>Sidebar Variants</h1>

        <?php if ($notice === 'saved'): ?>
            <div class="notice notice-success is-dismissible"><p>Sidebar variant saved.</p></div>
        <?php elseif ($notice === 'deleted'): ?>
            <div class="notice notice-success is-dismissible"><p>Sidebar variant deleted.</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('stwbpb_sidebar_variant_action'); ?>
            <input type="hidden" name="action" value="stwbpb_create_sidebar_variant" />
            <p><button type="submit" class="button button-primary">Add New Sidebar Variant</button></p>
        </form>

        <?php if (empty($variants)): ?>
            <p>No sidebar variants yet.</p>
        <?php else: ?>
            <div style="max-width:600px;margin-top:16px;">
                <?php foreach ($variants as $variant): ?>
                    <?php
                    $vid      = isset($variant['id'])    ? $variant['id']    : '';
                    $vtitle   = isset($variant['title']) ? $variant['title'] : '(untitled)';
                    $edit_url = admin_url('admin.php?page=stwbpb-sidebar-variant-edit&variant_id=' . urlencode($vid));
                    ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e0e0e0;">
                        <span><?php echo esc_html($vtitle); ?></span>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <a href="<?php echo esc_url($edit_url); ?>" class="button button-secondary">Edit</a>
                            <form method="post">
                                <?php wp_nonce_field('stwbpb_sidebar_variant_action'); ?>
                                <input type="hidden" name="action" value="stwbpb_delete_sidebar_variant" />
                                <input type="hidden" name="variant_id" value="<?php echo esc_attr($vid); ?>" />
                                <button type="submit" class="button" onclick="return confirm('Delete this sidebar variant?');">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// --- Edit page ---

function stwbpb_sidebar_variant_edit_page() {
    if (!current_user_can('manage_options')) return;

    $variant_id = isset($_GET['variant_id']) ? sanitize_text_field($_GET['variant_id']) : '';
    $variant    = stwbpb_sidebar_variants_get_by_id($variant_id);

    if (!$variant) {
        echo '<div class="wrap"><p>Sidebar variant not found. <a href="' . esc_url(admin_url('admin.php?page=stwbpb-sidebar-variants')) . '">Back to list</a></p></div>';
        return;
    }

    $title     = isset($variant['title'])     ? $variant['title']     : '';
    $side_left = !empty($variant['side_left']);
    $sections  = isset($variant['sections']) ? $variant['sections'] : array();
    $notice   = isset($_GET['swp_notice'])  ? sanitize_key($_GET['swp_notice'])  : '';
    $list_url = admin_url('admin.php?page=stwbpb-sidebar-variants');
    ?>
    <div class="wrap">
        <h1>Edit Sidebar Variant</h1>
        <p><a href="<?php echo esc_url($list_url); ?>">&larr; Back to Sidebar Variants</a></p>

        <?php if ($notice === 'empty_title'): ?>
            <div class="notice notice-error is-dismissible"><p>Title is required.</p></div>
        <?php elseif ($notice === 'duplicate_title'): ?>
            <div class="notice notice-error is-dismissible"><p>A sidebar variant with that title already exists. Please choose a unique title.</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('stwbpb_sidebar_variant_action'); ?>
            <input type="hidden" name="action" value="stwbpb_save_sidebar_variant" />
            <input type="hidden" name="stwbpb_variant[id]" value="<?php echo esc_attr($variant_id); ?>" />

            <table class="form-table" style="max-width:700px;">
                <tr>
                    <th scope="row"><label for="stwbpb-variant-title">Internal title</label></th>
                    <td>
                        <input id="stwbpb-variant-title" class="regular-text" type="text" name="stwbpb_variant[title]" value="<?php echo esc_attr($title); ?>" required />
                        <p class="description">Used in dropdowns to identify this sidebar. Must be unique.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stwbpb-variant-side-left">Show sidebar on the left</label></th>
                    <td>
                        <input id="stwbpb-variant-side-left" type="checkbox" name="stwbpb_variant[side_left]" value="1" <?php checked($side_left); ?> />
                        <p class="description">When checked, emits <code>side="left"</code> on the sidebar element. Default (unchecked) is right.</p>
                    </td>
                </tr>
            </table>

            <h2>Sections</h2>

            <div id="sidebar-sections-container"<?php if (empty($sections)) echo ' style="display:none"'; ?>>
                <?php foreach ($sections as $si => $sec):
                    $sec_type = isset($sec['type']) ? $sec['type'] : 'search';
                    $d_search = $sec_type !== 'search'           ? ' disabled' : '';
                    $d_rc     = $sec_type !== 'recent-comments'  ? ' disabled' : '';
                    $d_links  = $sec_type !== 'links'            ? ' disabled' : '';
                    $d_rp     = $sec_type !== 'recent-posts'     ? ' disabled' : '';
                    $prefix   = 'stwbpb_variant[sections][' . intval($si) . ']';
                    ?>
                    <div class="sidebar-section">
                        <div class="settings-option-div">
                            <label>Section type: </label>
                            <div class="spacerW10"></div>
                            <select name="<?php echo esc_attr($prefix); ?>[type]" class="sidebar-section-type">
                                <option value="search" <?php selected($sec_type, 'search'); ?>>Search</option>
                                <option value="recent-comments" <?php selected($sec_type, 'recent-comments'); ?>>Recent Comments</option>
                                <option value="links" <?php selected($sec_type, 'links'); ?>>Links</option>
                                <option value="recent-posts" <?php selected($sec_type, 'recent-posts'); ?>>Recent Posts</option>
                            </select>
                        </div>

                        <div class="sidebar-section-fields sidebar-type-search"<?php if ($sec_type !== 'search') echo ' style="display:none"'; ?>>
                            <div class="settings-option-div">
                                <label>Search action URL (use %s for the search term): </label>
                                <div class="spacerW10"></div>
                                <input class="single-text-input" type="text" name="<?php echo esc_attr($prefix); ?>[action]" value="<?php echo esc_attr($sec['action'] ?? ''); ?>"<?php echo esc_attr($d_search); ?> />
                            </div>
                            <div class="settings-option-div">
                                <label>Placeholder: </label>
                                <div class="spacerW10"></div>
                                <input class="single-text-input" type="text" name="<?php echo esc_attr($prefix); ?>[placeholder]" value="<?php echo esc_attr($sec['placeholder'] ?? ''); ?>"<?php echo esc_attr($d_search); ?> />
                            </div>
                            <div class="settings-option-div">
                                <label>Open results in: </label>
                                <div class="spacerW10"></div>
                                <select name="<?php echo esc_attr($prefix); ?>[target]"<?php echo esc_attr($d_search); ?>>
                                    <option value="_self" <?php selected($sec['target'] ?? '_self', '_self'); ?>>Same tab</option>
                                    <option value="_blank" <?php selected($sec['target'] ?? '_self', '_blank'); ?>>New tab</option>
                                </select>
                            </div>
                        </div>

                        <div class="sidebar-section-fields sidebar-type-recent-comments"<?php if ($sec_type !== 'recent-comments') echo ' style="display:none"'; ?>>
                            <div class="settings-option-div">
                                <label>Title: </label>
                                <div class="spacerW10"></div>
                                <input class="single-text-input" type="text" name="<?php echo esc_attr($prefix); ?>[title]" value="<?php echo esc_attr($sec['title'] ?? ''); ?>"<?php echo esc_attr($d_rc); ?> />
                            </div>
                            <div class="settings-option-div">
                                <label>Max number of comments: </label>
                                <div class="spacerW10"></div>
                                <input type="number" min="1" name="<?php echo esc_attr($prefix); ?>[max]" value="<?php echo esc_attr($sec['max'] ?? 5); ?>"<?php echo esc_attr($d_rc); ?> />
                            </div>
                            <div class="settings-option-div">
                                <label>Format (use {author} and {post}): </label>
                                <div class="spacerW10"></div>
                                <input class="single-text-input" type="text" name="<?php echo esc_attr($prefix); ?>[format]" value="<?php echo esc_attr($sec['format'] ?? ''); ?>"<?php echo esc_attr($d_rc); ?> />
                            </div>
                            <div class="settings-option-div">
                                <label>Include excerpt</label>
                                <div class="spacerW10"></div>
                                <input class="single-checkbox-input" type="checkbox" name="<?php echo esc_attr($prefix); ?>[include_excerpt]" value="1" <?php echo !empty($sec['include_excerpt']) ? 'checked' : ''; ?><?php echo esc_attr($d_rc); ?> />
                            </div>
                        </div>

                        <div class="sidebar-section-fields sidebar-type-links"<?php if ($sec_type !== 'links') echo ' style="display:none"'; ?>>
                            <div class="settings-option-div">
                                <label>Title: </label>
                                <div class="spacerW10"></div>
                                <input class="single-text-input" type="text" name="<?php echo esc_attr($prefix); ?>[title]" value="<?php echo esc_attr($sec['title'] ?? ''); ?>"<?php echo esc_attr($d_links); ?> />
                            </div>
                            <div class="sidebar-links">
                                <?php if (!empty($sec['links'])): foreach ($sec['links'] as $li => $lnk): ?>
                                    <div class="sidebar-link">
                                        <label>Link text: </label>
                                        <input type="text" name="<?php echo esc_attr($prefix); ?>[links][<?php echo esc_attr($li); ?>][text]" value="<?php echo esc_attr($lnk['text']); ?>"<?php echo esc_attr($d_links); ?> />
                                        <div class="spacerH10"></div>
                                        <label>Link URL: </label>
                                        <input type="text" name="<?php echo esc_attr($prefix); ?>[links][<?php echo esc_attr($li); ?>][url]" value="<?php echo esc_url($lnk['url']); ?>"<?php echo esc_attr($d_links); ?> />
                                        <div class="spacerH10"></div>
                                        <label>Open in: </label>
                                        <select name="<?php echo esc_attr($prefix); ?>[links][<?php echo esc_attr($li); ?>][target]"<?php echo esc_attr($d_links); ?>>
                                            <option value="" <?php selected($lnk['target'] ?? '', ''); ?>>Default</option>
                                            <option value="_self" <?php selected($lnk['target'] ?? '', '_self'); ?>>Same tab</option>
                                            <option value="_blank" <?php selected($lnk['target'] ?? '', '_blank'); ?>>New tab</option>
                                        </select>
                                        <div class="spacerH10"></div>
                                        <label>rel: </label>
                                        <input type="text" name="<?php echo esc_attr($prefix); ?>[links][<?php echo esc_attr($li); ?>][rel]" value="<?php echo esc_attr($lnk['rel'] ?? ''); ?>" placeholder="e.g. noopener noreferrer"<?php echo esc_attr($d_links); ?> />
                                        <div class="spacerH10"></div>
                                        <button type="button" class="remove-sidebar-link">Remove Link</button>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                            <button type="button" class="add-sidebar-link">Add Link</button>
                        </div>

                        <div class="sidebar-section-fields sidebar-type-recent-posts"<?php if ($sec_type !== 'recent-posts') echo ' style="display:none"'; ?>>
                            <div class="settings-option-div">
                                <label>Title: </label>
                                <div class="spacerW10"></div>
                                <input class="single-text-input" type="text" name="<?php echo esc_attr($prefix); ?>[title]" value="<?php echo esc_attr($sec['title'] ?? ''); ?>"<?php echo esc_attr($d_rp); ?> />
                            </div>
                            <div class="settings-option-div">
                                <label>Max number of posts: </label>
                                <div class="spacerW10"></div>
                                <input type="number" min="1" name="<?php echo esc_attr($prefix); ?>[max]" value="<?php echo esc_attr($sec['max'] ?? 5); ?>"<?php echo esc_attr($d_rp); ?> />
                            </div>
                        </div>

                        <div class="spacerH10"></div>
                        <button type="button" class="remove-sidebar-section">Remove Section</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" id="add-sidebar-section">Add Section</button>
            <div class="spacerH10"></div>
            <br/>
            <button type="submit" class="button button-primary">Save Variant</button>
        </form>
    </div>
    <?php
}
