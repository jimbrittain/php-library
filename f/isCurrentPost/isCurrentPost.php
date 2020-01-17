<?php
if (!function_exists('isCurrentPost')) {
    function isCurrentPost($postId)
    {
        if (isset($GLOBALS['post'])) {
            return ($postId == $GLOBALS['post']->ID);
        } else if (isset($GLOBALS['wp_query']) && isset($GLOBALS['wp_query']->post)) {
            return ($postId == $GLOBALS['wp_query']->post-ID);
        } else {
            return false;
        }
    }
}
