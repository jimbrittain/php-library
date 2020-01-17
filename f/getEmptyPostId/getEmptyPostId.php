<?php
    if (!function_exists('getEmptyPostId')) {
        function getEmptyPostId()
        {
            function goodNumber($n)
            {
                return (is_numeric($n) && (int) abs($n) == $n);
            }
            global $post;
            if (isset($post) && isset($post->ID) && goodNumber($post->ID)) {
                return $post->ID;
            }
            //@codingStandardsIgnoreStart - camel-case
            global $wp_query;
            if (isset($wp_query)) {
                if (isset($wp_query->post)
                    && isset($wp_query->post->ID)
                    && goodNumber($wp_query->post->ID)) {
                    return $wp_query->post->ID;
                } else {
                    if ($wp_query->have_posts()) {
                        $wp_query->the_post();
                        $id = $wp_query->post->ID;
                        $wp_query->rewind_posts();
                        return $id;
                    }
                }
                //@codingStandardsIgnoreEnd
            }
            //Fallback method has issues with custom post-types if called early, prior to wp_header
            if (isset($_SERVER['REQUEST_URI']) && function_exists('url_to_postid')) {
                $currentUrl = $_SERVER['REQUEST_URI'];
                $postId = url_to_postid($currentUrl);
                if (goodNumber($postId)) {
                    return $postId;
                }
            }
            return null;
        }
    }
