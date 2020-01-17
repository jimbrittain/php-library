<?php
defined('ABSPATH') or die('Script Error');
/**
Plugin Name: SEO Meta Content
Description: Meta Keyword and Description additions, script- activated by new SEOMetaContent(types or blank for all);
Version: 0.1
Author: Immature Dawn
Author URI: http://immaturedawn.co.uk
*/
if (!class_exists('SEOMetaContent')) {
    class SEOMetaContent
    {
        public static $types = array();
        public static $name = 'seo-meta-content';
        public static $descriptionName = 'seo-meta-description';
        public static $keywordsName = 'seo-meta-keywords';
        public function __construct($types)
        {
            self::setTypes($types);
            add_action('admin_init', array('SEOMetaContent', 'build'));
        }
        public static function getPostId($postId = '')
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
        public static function getDescription($postId = '')
        {
            $postId = self::getPostId($postId);
            if ($postId !== false) {
                return get_post_meta($postId, self::$descriptionName, true);
            }
            return '';
        }
        public static function getKeywords($postId = '')
        {
            $postId = self::getPostId($postId);
            if ($postId !== false) {
                return get_post_meta($postId, self::$keywordsName, true);
            }
            return '';
        }
        public static function setTypes($types = '')
        {
            if ($types == '') {
                self::$types = array();
            } else if (is_string($types)) {
                self::$types = array($types);
            } else if (is_array($types)) {
                self::$types = array();
                foreach ($types as $t) {
                    if (is_string($t)) {
                        self::$types[] = $t;
                    }
                }
            }
        }
        public static function addBox()
        {
            add_meta_box(
                'im-seo-meta-content',
                'SEO Meta Content',
                array('SEOMetaContent', 'edit'), 
                (count(self::$types) > 0) ? self::$types : null, 
                'side',
                'low'
            );
        }
        public static function build()
        {
            if (count(self::$types) > 0) {
                foreach (self::$types as $t) {
                    add_action('add_meta_boxes_'.$t, array('SEOMetaContent', 'addBox'));
                    add_action('save_post_'.$t, array('SEOMetaContent', 'save'), 10, 3);
                    add_action('publish_'.$t, array('SEOMetaContent', 'save'), 10, 3);
                }
                add_action('pre_post_update', array('SEOMetaContent', 'save'), 10, 3);
                add_action('save_post', array('SEOMetaContent', 'save'), 10, 3);
            } else {
                add_action('add_meta_boxes', array('SEOMetaContent', 'addBox'));
                add_action('pre_post_update', array('SEOMetaContent', 'save'), 10, 3);
                add_action('save_post', array('SEOMetaContent', 'save'), 10, 3);
            }
        }
        public static function save($postId, $post = '', $update = '')
        {
            global $post;
            if (!isset($_POST[self::$name]) || !wp_verify_nonce($_POST[self::$name], basename(__FILE__))) {
                return;
            }
            if (!current_user_can('edit_post', $postId)) {
                return;
            }
            //@codingStandardsIgnoreStart - doesn't like post_type
            if (count(self::$types) > 0 && !in_array($post->post_type, self::$types)) {
            //@codingStandardsIgnoreEnd
                return;
            }
            if (isset($_POST[self::$descriptionName]) && is_string($_POST[self::$descriptionName])) {
                $desc = $_POST[self::$descriptionName];
                $desc = urldecode($desc);
                $desc = html_entity_decode($desc, ENT_NOQUOTES);
                $desc = (strlen($desc) > 155) ? substr($desc, 0, 154) : $desc;
                $desc = str_replace('"', "'", $desc);
                $desc = htmlspecialchars($desc);
                update_post_meta($postId, self::$descriptionName, $desc);
            }
            if (isset($_POST[self::$descriptionName]) && is_string($_POST[self::$keywordsName])) {
                $keys = $_POST[self::$keywordsName];
                $keys = urldecode($keys);
                $keys = html_entity_decode($keys, ENT_NOQUOTES);
                $keys = str_replace('"', "'", $keys);
                $keys = htmlspecialchars($keys);
                update_post_meta($postId, self::$keywordsName, $keys);
            }
            return $postId;
        }
        public static function edit($post)
        {
            echo self::view($post);
        }
        public static function view($post)
        {
            wp_nonce_field(basename(__FILE__), self::$name);
            //@codingStandardsIgnoreStart - line-lengths
            $str = '
                <div>
                    <label for="'.self::$descriptionName.'">SEO Description</label>
                    <textarea name="'.self::$descriptionName.'" id="'.self::$descriptionName.'">'.get_post_meta($post->ID, self::$descriptionName, true).'</textarea><br />
                    <p>Your SEO description should be less than 155 characters (including spaces), this is checked when submitting.</p>
                    <hr />
                    <label for="'.self::$keywordsName.'">SEO Keywords</label>
                    <textarea name="'.self::$keywordsName.'" id="'.self::$keywordsName.'">'.get_post_meta($post->ID, self::$keywordsName, true).'</textarea><br />
                    <p>Your SEO keywords, separated with commas</p>
                </div>';
            //@codingStandardsIgnoreEnd
            return $str;
        }
    }
}
