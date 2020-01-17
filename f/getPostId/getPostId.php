<?php
if (!function_exists('getPostId')) {
    function getPostId($postId)
    {
        if ($postId === '') {
            if (isset($GLOBALS['post'])) {
                $postId = $GLOBALS['post']->ID;
            } else if (isset($GLOBALS['wp_query'])) {
                $postId = $GLOBALS['wp_query']->post->ID;
            }
        }
        if ((int) $postId == $postId && abs($postId) == $postId) {
            return $postId;
        } else { 
            return false; 
        }
    }
}

