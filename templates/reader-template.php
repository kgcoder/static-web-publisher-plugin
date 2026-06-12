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

$settings             = get_option('stwbpb_settings', []);
$permalink            = get_permalink($post->ID);
$title                = $post->post_title;
$htmlContent          = $post->post_content;
$connections_info     = get_post_meta($post->ID, '_static_web_connections_info', true);
$display_author_name  = $settings['display_author_name'] ?? '';
$display_publish_date = $settings['display_publish_date'] ?? '';
$embedPattern = '/<!-- wp:embed \{"url":"https:\/\/www\.youtube\.com\/(watch\?v=|embed\/)([^"]+)",.*\} -->.*<div class="wp-block-embed__wrapper">\s*(https:\/\/www\.youtube\.com\/(watch\?v=|embed\/)[^<]+)\s*<\/div>.*<!-- \/wp:embed -->/sU';

$htmlContent = preg_replace_callback($embedPattern, function ($matches) {
    $youtubeId = $matches[2];
    return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$youtubeId\" frameborder=\"0\" allowfullscreen></iframe>";
}, $htmlContent);

$htmlContent = stwbpb_strip_wp_tags($htmlContent);

$allowed_tags = wp_kses_allowed_html('post');
$allowed_tags['iframe'] = [
    'src' => true,
    'width' => true,
    'height' => true,
    'frameborder' => true,
    'allowfullscreen' => true,
    'referrerpolicy' => true,
    'sandbox' => true,
];

$panels_escaped = stwbpb_get_panels($post);

$reader_url = plugins_url('reader/', dirname(__FILE__));
$dist_url   = plugins_url('dist/', dirname(__FILE__));

$meta = [
    'panels'    => $panels_escaped,
    'permalink' => $permalink,
    'title'     => $title,
];

if (!empty($connections_info)) {
    $meta['connections'] = $connections_info;
}
?>

<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo esc_html($title); ?></title>

    <link rel="canonical" href="<?php echo esc_url($permalink); ?>">

    <link rel="stylesheet" href="<?php echo esc_url($reader_url . 'reader.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url($reader_url . 'ExportPage.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url($reader_url . 'PageInfo.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url($reader_url . 'themes/light.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url($reader_url . 'themes/dark.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url($reader_url . 'themes/sepia.css'); ?>">

    <script>
        window.vcReaderData = {
            assetsUrl: <?php echo wp_json_encode($reader_url . 'images/'); ?>,
            proxyUrl:  <?php echo wp_json_encode(home_url('/sw-proxy/')); ?>
        };
    </script>

    <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
        <script type="module" src="<?php echo esc_url($reader_url . 'readerStartUp.js'); ?>"></script>
    <?php else: ?>
        <script type="module" src="<?php echo esc_url($dist_url . 'reader.bundle.min.js'); ?>"></script>
    <?php endif; ?>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if (is_user_logged_in() && is_admin_bar_showing()): ?>
    <?php wp_admin_bar_render(); ?>
<?php endif; ?>

<div id="AllDocumentsContainer" class="theme-light">
    <div id="OneDocumentContainer">

        <div id="CurrentDocumentTopBar">
            <div id="CurrentDocumentTopBarRow" class="theme-light">

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

                <div class="spacer"></div>

                <a id="CurrentDocumentTitleLink" href="#"><span id="CurrentDocumentTitleSpan"></span></a>

                <div id="CurrentDocumentEmbeddingSymbol" class="DocumentTopBarButtonWrapper" title="This document embeds another document. Click on the title to view the embedded document separately."></div>

                <div id="mainDocSpinner"></div>
            </div>
        </div>

        <div id="CurrentDocumentMainRow" class="DocumentMainRow">

            <div id="CurrentDocumentLeftPanel" class="DocumentSidePanel theme-light"></div>

            <div id="CurrentDocument" class="DocumentColumn theme-light">
                <div id="CurrentDocumentTopPanel" class="DocumentTopPanel">
                    <a id="CurrentDocumentTopPanelLogoLink" class="TopPanelLogoLink" href="#">
                        <img id="CurrentDocumentTopPanelLogo" src="" width="150px" height="50px"/>
                        <span id="CurrentDocumentTopPanelTitle" class="TopPanelTitle"><?php echo esc_html($title); ?></span>
                    </a>
                    <div class="spacer"></div>
                    <div id="CurrentDocumentTopPanelOptionsRow" class="DocumentTopPanelOptionsRow"></div>
                    <div id="LeftSandwichButton" class="SandwichButton">
                        <div class="LeftSandwichLine"></div>
                        <div class="LeftSandwichLine SandwichMiddleLine"></div>
                        <div class="LeftSandwichLine"></div>
                    </div>
                </div>
                <div id="CurrentDocumentHeader" class="HeaderDiv"></div>
                <div id="CurrentDocumentMainDiv" class="hdoc-content">
                    <?php echo wp_kses($htmlContent, $allowed_tags); ?>
                    <a id="MainDocDownloadLink">Download main document</a>

                </div>
                <textarea id="CurrentDocumentTextarea" class="NoteEditingDiv"></textarea>
                <div id="CurrentDocumentBottomPanel" class="DocumentBottomPanel">
                    <div id="CurrentDocumentBottomPanelRow" class="DocumentBottomPanelRow"></div>
                    <div id="CurrentDocumentBottomPanelBottomMessage" class="DocumentBottomPanelBottomMessage"></div>
                </div>
                <div id="CurrentDocumentMainCollageDiv">
                    <canvas id="CurrentDocumentMainCollageCanvas" class="OneCollageCanvas"></canvas>
                </div>
                <div id="CurrentDocumentDropDownMenu" class="DocumentDropDownMenu"></div>
            </div>

            <div id="CurrentDocumentRightPanel" class="DocumentSidePanel theme-light"></div>

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
            <div class="spacer"></div>
            <a id="RightDocumentTitleLink"><span id="RightDocumentTitleSpan"></span></a>
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
<div id="multiple-links-popup" class="theme-light"></div>

<?php wp_footer(); ?>

</body>
</html>