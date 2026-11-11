<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Default values of the user-editable UI texts (editable so sites can
// translate them). An empty saved value always means "use the default".

define('STWBPB_COMMENTS_TITLE_DEFAULT',            'Comments');
define('STWBPB_NO_COMMENTS_MESSAGE_DEFAULT',       'No comments yet');
define('STWBPB_REPLY_BUTTON_LABEL_DEFAULT',        'Reply');
define('STWBPB_LEAVE_COMMENT_LABEL_DEFAULT',       'Leave a comment');
define('STWBPB_LOAD_MORE_COMMENTS_LABEL_DEFAULT',  'Load more comments');
define('STWBPB_OPEN_IN_NEW_TAB_LABEL_DEFAULT',     'Open in a new tab to view comments');
define('STWBPB_FORM_TITLE_DEFAULT',                'Leave a comment');
define('STWBPB_REPLYING_TO_LABEL_DEFAULT',         'Replying to %s');
define('STWBPB_COMMENTING_ON_LABEL_DEFAULT',       'Commenting on: %s');
define('STWBPB_NAME_LABEL_DEFAULT',                'Name');
define('STWBPB_EMAIL_LABEL_DEFAULT',               'Email');
define('STWBPB_COMMENT_LABEL_DEFAULT',             'Comment');
define('STWBPB_SUBMIT_BUTTON_LABEL_DEFAULT',       'Post comment');
define('STWBPB_SUBMITTED_TITLE_DEFAULT',           'Comment submitted');
define('STWBPB_THANK_YOU_MESSAGE_DEFAULT',         'Thank you for your comment!');
define('STWBPB_AWAITING_APPROVAL_MESSAGE_DEFAULT', 'It will appear once approved.');
define('STWBPB_ERROR_SECURITY_DEFAULT',            'Security check failed. Please try again.');
define('STWBPB_ERROR_CLOSED_DEFAULT',              'Commenting is closed for this post.');
define('STWBPB_ERROR_INVALID_PARENT_DEFAULT',      'Invalid parent comment.');
define('STWBPB_ERROR_NAME_REQUIRED_DEFAULT',       'Please enter your name.');
define('STWBPB_ERROR_INVALID_EMAIL_DEFAULT',       'Please enter a valid email address.');
define('STWBPB_ERROR_COMMENT_REQUIRED_DEFAULT',    'Please enter a comment.');
define('STWBPB_ERROR_SAVE_FAILED_DEFAULT',         'Could not save your comment. Please try again.');
define('STWBPB_RW_BADGE_TEXT_DEFAULT',             "This page is part of Reader's Web");
define('STWBPB_RW_BADGE_POPUP_TEXT_DEFAULT',       "This page is part of Reader's Web. To view it properly, install the RW Reader extension and reload this page.");

// Setting key => default text, for every user-editable UI text.
function stwbpb_get_default_texts() {
    return array(
        'comments_title'            => STWBPB_COMMENTS_TITLE_DEFAULT,
        'no_comments_message'       => STWBPB_NO_COMMENTS_MESSAGE_DEFAULT,
        'reply_button_label'        => STWBPB_REPLY_BUTTON_LABEL_DEFAULT,
        'leave_comment_label'       => STWBPB_LEAVE_COMMENT_LABEL_DEFAULT,
        'load_more_comments_label'  => STWBPB_LOAD_MORE_COMMENTS_LABEL_DEFAULT,
        'open_in_new_tab_label'     => STWBPB_OPEN_IN_NEW_TAB_LABEL_DEFAULT,
        'form_title'                => STWBPB_FORM_TITLE_DEFAULT,
        'replying_to_label'         => STWBPB_REPLYING_TO_LABEL_DEFAULT,
        'commenting_on_label'       => STWBPB_COMMENTING_ON_LABEL_DEFAULT,
        'name_label'                => STWBPB_NAME_LABEL_DEFAULT,
        'email_label'               => STWBPB_EMAIL_LABEL_DEFAULT,
        'comment_label'             => STWBPB_COMMENT_LABEL_DEFAULT,
        'submit_button_label'       => STWBPB_SUBMIT_BUTTON_LABEL_DEFAULT,
        'submitted_title'           => STWBPB_SUBMITTED_TITLE_DEFAULT,
        'thank_you_message'         => STWBPB_THANK_YOU_MESSAGE_DEFAULT,
        'awaiting_approval_message' => STWBPB_AWAITING_APPROVAL_MESSAGE_DEFAULT,
        'error_security'            => STWBPB_ERROR_SECURITY_DEFAULT,
        'error_closed'              => STWBPB_ERROR_CLOSED_DEFAULT,
        'error_invalid_parent'      => STWBPB_ERROR_INVALID_PARENT_DEFAULT,
        'error_name_required'       => STWBPB_ERROR_NAME_REQUIRED_DEFAULT,
        'error_invalid_email'       => STWBPB_ERROR_INVALID_EMAIL_DEFAULT,
        'error_comment_required'    => STWBPB_ERROR_COMMENT_REQUIRED_DEFAULT,
        'error_save_failed'         => STWBPB_ERROR_SAVE_FAILED_DEFAULT,
        'rw_badge_text'             => STWBPB_RW_BADGE_TEXT_DEFAULT,
        'rw_badge_popup_text'       => STWBPB_RW_BADGE_POPUP_TEXT_DEFAULT,
    );
}

// Returns the saved text for $key, or its default when the saved value is
// missing or empty.
function stwbpb_get_text($settings, $key) {
    if (is_array($settings) && isset($settings[$key]) && is_string($settings[$key]) && trim($settings[$key]) !== '') {
        return $settings[$key];
    }
    $defaults = stwbpb_get_default_texts();
    return isset($defaults[$key]) ? $defaults[$key] : '';
}
