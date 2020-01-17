<?php
if (!function_exists('getExcerptForPostId')) {
    function getExcerptForPostId($postId = '', $fallbackStr = '')
    {
        $postId = (function_exists('getPostId')) ? getPostId($postId) : false;
        $excerpt = (is_string($fallbackStr) && strlen($fallbackStr) > 0) ? $fallbackStr : '';
        if ($postId !== false) {
            if (!isCurrentPost($postId)) {
                $excerpt = get_post_field('post_excerpt', $postId);
                $excerpt = apply_filters('the_excerpt', $tp);
            } else {
                $excerpt = get_the_excerpt();
            }
        }
        return (strlen($excerpt) > 0) ? $excerpt : $fallbackStr;
    }
}
