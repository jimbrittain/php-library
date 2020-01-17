<?php
/**
 * @function wordpressGetAttachmentUrls
 * @param $id {String} - if not attempts to guess based on $GLOBALS
 * @return {Array} of file paths
 * @requires getPostId
 **/
function wordpressGetAttachmentUrls($id = null)
{
    if ($id === null 
        && function_exists('getPostId')) {
        $id = getPostId();
    }
    $paths = array();
    if (function_exists('get_children')
        && function_exists('wp_get_attachment_url')
        && $id !== null) {
        $attached = get_children(
            array(
                'post_parent' => $id,
                'post_type' => 'attachment',
                'post_status' => 'any'
            )
        );
        foreach ($attached as $a) {
            $r = wp_get_attachment_url($a->ID);
            if (!empty($r)) {
                $paths[] = $r;
            }
        }
    }
    return $paths;
}
