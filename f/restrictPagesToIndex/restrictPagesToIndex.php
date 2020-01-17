<?php
if (!function_exists('restrictPagesToIndex')) {
    function restrictPagesToIndex()
    {
        //@codingStandardsIgnoreStart - line-length
        $currentPost = (isset($GLOBALS['post'])) ? $GLOBALS['post'] : ((isset($GLOBALS['wp_query']) && isset($GLOBALS['wp_query']->post)) ? $GLOBALS['wp_query']->post : false);
        //@codingStandardsIgnoreEnd
        //@codingStandardsIgnoreStart - camelcase
        if ($currentPost !== false && isset($currentPost->post_type) && $currentPost->post_type === 'page' && !is_front_page()) {
        //@codingStandardsIgnoreEnd
            wp_redirect(home_url('/index.php'));
            exit; 
        }
    }
}
