<?php
    if (!(function_exists('getPostIdFromSlug'))) {
        function getPostIdFromSlug($slug = null, $postType = null)
        {
            if ($slug && is_string($slug)) {
                $arr = array();
                if (is_string($postType)) {
                    $arr[] = $postType;
                } else if (is_array($postType)) {
                    $arr = $postType;
                } else {
                    $arr = get_post_types();
                }
                $wpPage = get_page_by_path($slug, OBJECT, $arr);
                if ($wpPage) {
                    return $wpPage->ID;
                }
            }
            return null;
        }
    }
