<?php
    /**
    @method findUpperMostParentPostId
    @requires Wordpress, getEmptyPostId, wp_get_post_parent_id
    @attempts to resolve $postId if none supplied
     */
    if (!function_exists('findUpperMostParentPostId')) {
        function findUpperMostParentPostId($postId = null)
        {
            $postId = ($postId === null) ? getEmptyPostId() : $postId;
            $parentId = false;
            $done = false;
            while (!$done) {
                $postId = wp_get_post_parent_id($postId);
                if ($postId != false) {
                    $parentId = $postId;
                    continue;
                } else {
                    $done = true;
                }
            }
            return $parentId;
        }
    }
