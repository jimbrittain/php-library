<?php
if (!function_exists('processPostId')) {
    /**
     * @method processPostId
     * @param $postId {Int}
     * @param $post {WP_Post}
     * @description - after difficulties with the returns of WP_Post->ID and WP_Post
     *      from actions, function to gain more confidence in the returned values
     * @returns {Array} std int keys, each value is a stdClass;
     *      stdClass {
     *          'id' = {interger},
     *          'post' = {WP_Post}
     *      }
     */
    function processPostId($postId, $post = '')
    {
        $arr = array();
        if (!is_array($postId)) {
            $postId = array($postId);
            $post = array($post);
        }
        //foreach ($postId as $p) {
        for ($n = 0, $nmax = max(count($postId), count($post)); $n<$nmax; $n += 1) {
            $i = (isset($postId[$n])) ? $postId[$n] : '';
            $p = (isset($post[$n])) ? $post[$n] : '';
            $a = new stdClass();
            $a->id = '';
            $a->post = '';
            if (class_exists('WP_Post') && is_a($p, 'WP_Post')) {
                $a->post = $p;
                $a->id = $p->ID;
            } else if (class_exists('WP_Post') && is_a($i, 'WP_Post')) {
                $a->post = $i;
                $a->id = $i->ID;
            } else if (is_numeric($i) && (int) $i === $i && $i > 0) {
                $a->id = $i;
                if (function_exists('get_post')) {
                    $a->post = get_post($i);
                }
            }
            if ($a->id !== '') {
                $arr[] = $a;
            }
        }
        return $arr;
    }
}
