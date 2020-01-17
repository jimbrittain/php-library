<?php
    /**
    @method unparentedOnly
    @requires Wordpress, get_permalink, getEmptyPostId, singleStringArray, findUpperMostParentPostId
    @param {Number (int)} $postId - will attempt to resolve with getEmptyPostId if not supplied/supplied null
    @param {String|Array} $class - constrains to only particular post_types, if null = all post_types
    @description - where able, permantly redirects (301) to top-most ancestor
     */
    if (!function_exists('unparentedOnly')) {
        function unparentedOnly($postId = null, $class = array())
        {
            $postId = ($postId == null) ? getEmptyPostId() : $postId;
            $class = singleStringArray($class);
            if (count($class) > 0 && !in_array(get_post_type($postId), $class)) {
                return false;
            }
            $parentId = findUpperMostParentPostId($postId);
            if ($parentId != false) { //wide so if null covered
                if (!headers_sent()) {
                    $url = get_permalink($parentId);
                    $url = substr($url, strlen(home_url('/')));
                    header("Location: /".$url, TRUE, 301);
                }
            }
        }
    }
