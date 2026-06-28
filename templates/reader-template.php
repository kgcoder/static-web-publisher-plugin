<?php

/*
 * Converted from original HTML file in Visible Connections Chrome Extension
 *
 * Copyright (c) 2025 Karen Grigorian
 * Licensed under the MIT License.
 *
 * Original source was adapted into PHP for use in the Static Web Publisher WordPress plugin.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


global $post;

if (!$post) {
    status_header(404);
    echo 'Page not found';
    return;
}

$stwbpb_settings      = get_option('stwbpb_settings', []);
$stwbpb_permalink            = get_permalink($post->ID);
$title                = $post->post_title;
$stwbpb_html_content          = $post->post_content;
$stwbpb_connections_info     = get_post_meta($post->ID, '_static_web_connections_info', true);

$stwbpb_doc_type = stwbpb_get_effective_doc_type($post);
if ($stwbpb_doc_type === 'CDOC') {
    $stwbpb_doc_source   = stwbpb_build_cdoc_source($post);
    $stwbpb_source_class = 'cdoc-source';
} elseif ($stwbpb_doc_type === 'CONDOC') {
    $stwbpb_doc_source   = stwbpb_build_condoc_source($post);
    $stwbpb_source_class = 'condoc-source';
} else {
    $stwbpb_doc_source   = null;
    $stwbpb_source_class = 'hdoc-content';
}

$stwbpb_embed_pattern = '/<!-- wp:embed \{"url":"https:\/\/www\.youtube\.com\/(watch\?v=|embed\/)([^"]+)",.*\} -->.*<div class="wp-block-embed__wrapper">\s*(https:\/\/www\.youtube\.com\/(watch\?v=|embed\/)[^<]+)\s*<\/div>.*<!-- \/wp:embed -->/sU';

$stwbpb_html_content = preg_replace_callback($stwbpb_embed_pattern, function ($matches) {
    $youtubeId = $matches[2];
    return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$youtubeId\" frameborder=\"0\" allowfullscreen></iframe>";
}, $stwbpb_html_content);

$stwbpb_html_content = stwbpb_strip_wp_tags($stwbpb_html_content);

$stwbpb_allowed_tags = wp_kses_allowed_html('post');
$stwbpb_allowed_tags['iframe'] = [
    'src' => true,
    'width' => true,
    'height' => true,
    'frameborder' => true,
    'allowfullscreen' => true,
    'referrerpolicy' => true,
    'sandbox' => true,
];

$stwbpb_panels_escaped = stwbpb_get_panels($post);
$stwbpb_seo_panels = !empty($stwbpb_panels_escaped) ? stwbpb_get_seo_panel_data($stwbpb_panels_escaped) : null;

$stwbpb_reader_url = plugins_url('reader/', dirname(__FILE__));
$stwbpb_dist_url   = plugins_url('dist/', dirname(__FILE__));

$stwbpb_meta = [
    'panels'    => $stwbpb_panels_escaped,
    'permalink' => $stwbpb_permalink,
    'title'     => $title,
];

if (!empty($stwbpb_connections_info)) {
    $stwbpb_meta['connections'] = $stwbpb_connections_info;
}
?>

<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>document.documentElement.classList.add('js-enabled');</script>

    <title><?php echo esc_html($title); ?></title>

    <link rel="canonical" href="<?php echo esc_url($stwbpb_permalink); ?>">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if (is_user_logged_in() && is_admin_bar_showing()): ?>
    <?php wp_admin_bar_render(); ?>
<?php endif; ?>

<div id="ui-root" class="theme-light">
    <div id="AllDocumentsContainer">
        <div id="OneDocumentContainer">

            <div id="CurrentDocumentTopBar">
                <div id="CurrentDocumentTopBarRow">

                    <div id="CurrentDocumentInfoButtonWrapper" class="DocumentTopBarButtonWrapper">
                        <div title="Page info" id="CurrentDocumentInfoButton1" class="DocumentTopBarButton" href="#">
                            <span id="CurrentDocumentInfoButtonCountDiv" class="CurrentDocumentTopButtonCountDiv"></span>
                        </div>
                    </div>

                    <div title="Download all connected documents" id="CurrentDocumentDownloadAllDocsButton" class="DocumentTopBarButtonWrapper"></div>
                    <div title="Toggle full screen" id="CurrentDocumentFullScreenButton" class="DocumentTopBarButtonWrapper"></div>
                    <div id="CurrentDocumentExportButton" class="DocumentTopBarButtonWrapper"></div>
                    <div title="Veiw Page Source" id="CurrentDocumentSourceCodeButton" class="DocumentTopBarButtonWrapper"></div>
                    <div title="Center collage" id="CurrentDocumentCenterCollageButton" class="DocumentTopBarButtonWrapper"></div>
                    <div id="CurrentDocumentRightPanelButton" class="DocumentTopBarButtonWrapper"></div>
                    <div id="CurrentDocumentLeftPanelButton" class="DocumentTopBarButtonWrapper"></div>

                    <?php if (!empty($stwbpb_settings['show_promotion_button'])): ?>
                    <div id="PromotionButton" class="DocumentTopBarButtonWrapper"></div>
                    <?php endif; ?>


                    <div class="spacer10px"></div>

                    <div class="TitleContainer">
                        <span id="CurrentDocumentOptionalTitleSpan" style="display: none;"></span>
                        <a id="CurrentDocumentTitleLink" href="#"><span id="CurrentDocumentTitleSpan0" class="DocumentTitleSpan"></span></a>
                    </div>

                    <div id="CurrentDocumentCopyButton" class="DocumentTopBarButtonWrapper" title="This document is a copy of another document."></div>

                    <div id="CurrentDocumentEmbeddingSymbol" class="DocumentTopBarButtonWrapper" title="This document embeds another document. Click on the title to view the embedded document separately."></div>

                    <div id="mainDocSpinner"></div>
                </div>
            </div>

            <div id="CurrentDocumentMainRow" class="DocumentMainRow">

                <div id="CurrentDocumentLeftPanel" class="DocumentSidePanel"></div>

                <div id="CurrentDocument" class="DocumentColumn">
                    <div id="CurrentDocumentTopPanel" class="DocumentTopPanel">
                        <a id="CurrentDocumentTopPanelLogoLink" class="TopPanelLogoLink" href="<?php echo esc_url($stwbpb_seo_panels['logo_href'] ?? $stwbpb_seo_panels['site_name_href'] ?? '#'); ?>">
                            <img id="CurrentDocumentTopPanelLogo" src="<?php echo esc_url($stwbpb_seo_panels['logo_src'] ?? ''); ?>" width="150px" height="50px"<?php if (empty($stwbpb_seo_panels['logo_src'])) echo ' style="display:none"'; ?>/>
                            <span id="CurrentDocumentTopPanelTitle" class="TopPanelTitle"<?php if (empty($stwbpb_seo_panels['site_name'])) echo ' style="display:none"'; ?>><?php echo esc_html($stwbpb_seo_panels['site_name'] ?? $title); ?></span>
                        </a>
                        <div class="spacer"></div>
                        <div id="CurrentDocumentTopPanelOptionsRow" class="DocumentTopPanelOptionsRow"><?php
                        if (!empty($stwbpb_seo_panels['top_links'])) {
                            foreach ($stwbpb_seo_panels['top_links'] as $stwbpb_tl) {
                                echo '<a href="' . esc_url($stwbpb_tl['href']) . '">' . esc_html($stwbpb_tl['text']) . '</a>';
                            }
                        }
                        ?></div>
                        <div id="LeftSandwichButton" class="SandwichButton">
                            <div class="LeftSandwichLine"></div>
                            <div class="LeftSandwichLine SandwichMiddleLine"></div>
                            <div class="LeftSandwichLine"></div>
                        </div>
                    </div>

                    <div id="CurrentDocumentInnerRow">

                        <div id="CurrentDocumentBody">
                            <div id="CurrentDocumentHeader" class="HeaderDiv"><h1><?php echo esc_html($title); ?></h1></div>
                            <div id="CurrentDocumentMainDiv" class="PresentationDiv<?php echo ($stwbpb_doc_source !== null) ? '' : ' hdoc-content'; ?>">
                                <?php if ($stwbpb_doc_source !== null): ?>
                                    <script type="application/json" style="display:none;" id="<?php echo esc_attr($stwbpb_source_class); ?>"><?php echo wp_json_encode(['source' => $stwbpb_doc_source]); ?></script>
                                <?php else: ?>
                                    <?php echo wp_kses($stwbpb_html_content, $stwbpb_allowed_tags); ?>
                                    <a id="MainDocDownloadLink">Download main document</a>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div id="CurrentDocumentSidebar" class="SideBar"></div>

                    </div>

                    <div id="CurrentDocumentBottomBar" class="BottomBar"></div>

                    <div id="CurrentDocumentPostNavBar" class="PostNavBar"></div>

                    <div id="CurrentDocumentBottomPanel" class="DocumentBottomPanel">
                        <div id="CurrentDocumentBottomPanelRow" class="DocumentBottomPanelRow"><?php
                        if (!empty($stwbpb_seo_panels['bottom_sections'])) {
                            foreach ($stwbpb_seo_panels['bottom_sections'] as $stwbpb_bs) {
                                echo '<section';
                                if (!empty($stwbpb_bs['title'])) {
                                    echo ' title="' . esc_attr($stwbpb_bs['title']) . '"';
                                }
                                echo '>';
                                foreach ($stwbpb_bs['links'] as $stwbpb_bl) {
                                    echo '<a href="' . esc_url($stwbpb_bl['href']) . '">' . esc_html($stwbpb_bl['text']) . '</a>';
                                }
                                echo '</section>';
                            }
                        }
                        ?></div>
                        <div id="CurrentDocumentBottomPanelBottomMessage" class="DocumentBottomPanelBottomMessage"><?php echo isset($stwbpb_seo_panels['bottom_message']) ? esc_html($stwbpb_seo_panels['bottom_message']) : ''; ?></div>
                    </div>
                    <div id="CurrentDocumentMainCollageDiv">
                        <canvas id="CurrentDocumentMainCollageCanvas" class="OneCollageCanvas"></canvas>
                    </div>
                    <div id="CurrentDocumentDropDownMenu" class="DocumentDropDownMenu"></div>
                </div>

                <div id="CurrentDocumentRightPanel" class="DocumentSidePanel"></div>

                <div id="CurrentDocumentInfoContainer">
                    <div id="CurrentDocumentInfo"></div>
                </div>

                <div id="CurrentDocumentExportContainer"></div>

            </div>

        </div>

        <div id="middle-canvas-topDiv">
            <svg id="svgArrow" width="28" height="12" viewBox="0 0 28 12" fill="none" xmlns="http://www.w3.org/2000/svg"></svg>
            <a id="LinksOpenButton"></a>
        </div>

        <div id="middle-space-div"></div>

        

        <div id="AllRightDocumentsContainer">
            <div id="RightDocumentsTabsContainer"></div>
            <div id="RightDocumentsTopBar">
                <div title="Veiw Page Source" id="RightDocumentSourceCodeButton" class="DocumentTopBarButtonWrapper"></div>
                <div id="RightDocumentRightPanelButton" class="DocumentTopBarButtonWrapper"></div>
                <div id="RightDocumentLeftPanelButton" class="DocumentTopBarButtonWrapper"></div>
                <div title="Center collage" id="RightDocumentCenterCollageButton" class="DocumentTopBarButtonWrapper"></div>
            
                <div class="spacer10px"></div>

                <div class="TitleContainer">
                    <span id="RightDocumentOptionalTitleSpan" style="display: none;"></span>
                    <a id="RightDocumentTitleLink"><span id="RightDocumentTitleSpan" class="DocumentTitleSpan"></span></a>
                </div>

                <div id="RightDocumentCopyButton" class="DocumentTopBarButtonWrapper" title="This document is a copy of another document."></div>

            </div>
            <div id="RightDocumentCollectionContainer"></div>
            <div id="RightDocumentExportContainer"></div>
        </div>

        <canvas id="flinks-canvas"></canvas>
    </div>

    <div id="LinksListContainerDiv">
        <div id="LinksListTopRow">
            <div id="LinksListTopRowLeftSortButtonContainer"></div>
            <div id="LinksListTopRowMiddleSpacer"></div>
            <div id="LinksListTopRowRightSortButtonContainer"></div>
            <div id="LinksListTopRowEndSpacer"></div>
        </div>
        <div id="FlinksScrollDiv" class="scroll-container"></div>
        <span id="LinksListModificationMessage">Floating links were modified</span>
        <div id="LinksListBottomRow">
            <button id="LinksListCloseButton" class="CancelButton">Close</button>
            <div class="LinksListBottomRowMiddleSpacer"></div>
            <button id="LinksListOriginalLinksButton" class="CancelButton">Show original flinks</button>
            <div id="LinksListOriginalLinksSpacer" class="LinksListBottomRowMiddleSpacer"></div>
            <button id="LinksListFixButton" class="ActionButton">Fix all</button>
        </div>
    </div>

    <div id="spinner" class="spinner"></div>
    <div id="CurrentUrl"></div>
    <div id="multiple-links-popup"></div>
</div>
<?php wp_footer(); ?>

</body>
</html>