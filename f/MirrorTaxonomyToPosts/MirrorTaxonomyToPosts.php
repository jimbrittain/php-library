<?php
/*
Plugin Name: Mirror Taxonomy To Posts Class
Description: Enables mirroring a taxonomy to a class
Version: 0.1
Author: Immature Dawn
License: GPL2
Copyright 2017 Jim Brittain jim@immaturedawn.co.uk
 */
if (!class_exists('IM_MirrorTaxonomyToPosts')) {
    /**
     * @class IM_MirrorTaxonomyToPosts
     * @param $type {String}
     * @param $args {Array}
     * @description Used to create a slug mirrored taxonomy of a particular
     *      post-type, which is self-organising and used for attaching and
     *      relating content
     */
    class IM_MirrorTaxonomyToPosts
    {
        private $_taxonomy = '';
        private $_type = '';
        private $_hierachical = false;
        private $_attached = '';
        private $_valid = false;
        private $_desiredTypes = array();
        /**
         * @method __construct
         * @constructor
         * @param $type {String} identifier for post_type, required
         * @param $args {Array} arguments, optional
         */
        public function __construct($type, $args = '')
        {
            $this->dependencies();
            //should really do some kind of validation;
            $this->_type = $type;
            //should be done after plugins loaded at earliest!
            if (!empty($args)) {
                $this->setDesiredTypes($args);
            }
            $this->create($args);
            $this->enable();
        }
        public function setDesiredTypes($args)
        {
            $args = (is_string($args)) ? array($args) : $args;
            if (is_array($args)) {
                for ($i=0; $i<count($args); $i += 1) {
                    $a = $args[$i];
                    if (!empty($a) && is_string($a) && !in_array($a, $this->_desiredTypes)) {
                        $this->_desiredTypes[] = $a;
                    }
                }
                return true;
            } else {
                return false;
            }
        }
        /**
         * @method dependencies
         * @description checks whether class/function exists, if not
         *  assuming same directory, attempts to load
         */
        private function dependencies()
        {
            if (!class_exists('IM_Taxonomy')) {
                include_once('IM_Taxonomy.php');
            }
            if (!function_exists('processPostId')) {
                include_once('processPostId.php');
            }
        }
        public function getLinked($slug, $postTypes = '')
        {
            $slugs = IM_Taxonomy::termsExist($slug);
            if (count($slugs) > 0) {
                $pt = $this->returnAttached($args);
                if (count($pt) > 0) {
                    $args = array(
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'tax_query' => array(
                            array (
                                'taxonomy' => $this->_taxonomy, 
                                'field' => 'name', 
                                'terms' => $slugs, 
                            )
                        ), 
                        'post_type' => $pt,
                        'nopaging' => true, 
                    );
                    return get_posts($args);
                }
            }
            return array();
        }
        /**
         * @method getLinkedIds
         * @param $slug {String|Array}
         * @param $postTypes {String|Array}
         * @return {Array} of ID Intergers
         * @description - returns an array of the associated posts, both slugs and postTypes are validated
         */
        public function getLinkedIds($slug, $postTypes = '')
        {
            $r = $this->getLinked($slug, $postTypes);
            $a = array();
            foreach ($r as $p) {
                if (is_object($p) && property_exists($p, 'ID')) {
                    $a[] = $p->ID;
                }
            }
            return $a;
        }
        /**
         * @method setAttached
         * @private
         * @param $args {String|Array}
         * @returns $this->_attached
         * @description Sets the linked post types done once at creation
         */
        private function setAttached()
        {
            $args = $this->_desiredTypes;
            $attached = array();
            if ($args === '') {
                $attached[] = 'attachment';
            } else {
                if (is_string($args)) {
                    $args = array($args);
                }
                if (is_array($args)) {
                    foreach ($args as $a) {
                        if (is_string($a)) {
                            //removed if post_type_exists check in case of staggered creation;
                            $attached[] = $a;
                        }
                    }
                } else {
                    $attached[] = 'attachment';
                }
            }
            $this->_attached = $attached;
            return $this->_attached;
        }
        public function returnAttached($s = '')
        {
            if ($s === '') {
                return $this->_attached;
            } else {
                if (is_string($s)) {
                    return (in_array($s, $this->_attached)) ? array($s) : array();
                } else {
                    $r = array();
                    if (is_array($s)) {
                        foreach ($s as $a) {
                            if (is_string($a) && in_array($a, $this->_attached)) {
                                $r[] = $a;
                            }
                        }
                    }
                    return $r;
                }
            }
        }
        public function create()
        {
            $this->_taxonomy = 'mirrortax-'.$this->_type;
            $this->hierachical = is_post_type_hierarchical($this->_type);
            $this->setAttached();
            register_taxonomy(
                $this->_taxonomy,
                $this->_attached,
                array(
                    'labels' => array(
                        'name' => 'mirrortax-'.$this->_type,
                        'singular_name' => 'mirrortax-'.$this->_type.'(s)'
                    ),
                    'public',
                    'show_ui',
                    'description' => 'Used to match Taxonomy to Post Type',
                    'hierarchical' => $this->_hierachical,
                    'rewrite',
                )
            );
        }
        public function createTerm($slug, $args = '')
        {
            if (IM_Taxonomy::exists($slug, $this->_taxonomy)) {
                return false;
            } else {
                return IM_Taxonomy::create($slug, $this->_taxonomy, $args);
            }
        }
        public function deleteTerm($slug)
        {
            if (IM_Taxonomy::exists($slug, $this->_taxonomy)) {
                return IM_Taxonomy::delete($slug, $this->_taxonomy);
            } else {
                return false;
            }
        }
        public function getPostNames()
        {
            $args = array(
                'post_type' => $this->_type,
                'posts_per_page' => 1000
            );
            $posts = get_posts($args);
            $a = array();
            foreach ($posts as $p) {
                //@codingStandardsIgnoreStart
                $a[] = $p->post_name;
                //@codingStandardsIgnoreEnd
            }
            return $a;
        }
        public function checkExisting()
        {
            $taxTerms = $this->getTermNames();
            $postSlugs = $this->getPostNames();
            foreach ($taxTerms as $t) {
                if (in_array($t, $postSlugs)) {
                    $i = array_search($t, $postSlugs);
                    array_splice($postSlugs, $i, 1);
                } else {
                    $this->deleteTerm($t);
                }
            }
            $it = 0;
            $maxIt = count($postSlugs) * 5;
            for ($n = 0; $n < count($postSlugs); $n += 1) {
                $r = $this->createTerm($postSlugs[$n]);
                if ($r === 0) {
                   $postSlugs[] = $postSlugs[$n];
                }
                array_splice($postSlugs, $n, 1);
                $n -= 1;
                $it += 1;
                if ($it > $maxIt) {
                    break;
                }
            }
        }
        public function isValid()
        {
        }
        public function enable()
        {
            add_action('save_post_'.$this->_type, array($this, 'add'), 10, 2);
            add_action('save_post', array($this, 'add'), 10, 2);
            add_action('edit_post', array($this, 'add'), 10, 2);
            add_action('pre_post_update', array($this, 'add'), 10, 2);
            add_action('wp_insert_post', array($this, 'add'), 10, 2);
            add_action('publish_post', array($this, 'add'), 10, 2);
            add_action('publish_'.$this->_type, array($this, 'add'), 10, 2);
            add_action('post_updated', array($this, 'add'), 10, 2);
            add_action('untrash_post', array($this, 'add'), 10, 2);
            add_action('untrashed_post', array($this, 'add'), 10, 2);
            
            add_action('wp_trash_post', array($this, 'remove'), 10, 2);
            add_action('deleted_post', array($this, 'remove'), 10, 2);
            add_action('trash_post', array($this, 'remove'), 10, 2);
            add_action('trashed_post', array($this, 'remove'), 10, 2);
            add_action('before_delete_post', array($this, 'remove'), 10, 2);
            add_action('delete_post', array($this, 'remove'), 10, 2);
            add_action('delete_post_'.$this->_type, array($this, 'remove'), 10, 2);
            add_action('wp_delete_post', array($this, 'remove'), 10, 2);
            add_action('deleted_post', array($this, 'remove'), 10, 2);
        }
        public function disable()
        {
            remove_action('save_post_'.$this->_type, array($this, 'add'), 10);
            remove_action('save_post', array($this, 'add'), 10);
            remove_action('edit_post', array($this, 'add'), 10);
            remove_action('pre_post_update', array($this, 'add'), 10);
            remove_action('wp_insert_post', array($this, 'add'), 10);
            remove_action('publish_post', array($this, 'add'), 10);
            remove_action('publish_'.$this->_type, array($this, 'add'), 10);
            remove_action('post_updated', array($this, 'add'), 10);
            remove_action('untrash_post', array($this, 'add'), 10);
            remove_action('untrashed_post', array($this, 'add'), 10);
            
            remove_action('wp_trash_post', array($this, 'remove'), 10);
            remove_action('deleted_post', array($this, 'remove'), 10);
            remove_action('trash_post', array($this, 'remove'), 10);
            remove_action('trashed_post', array($this, 'remove'), 10);
            remove_action('before_delete_post', array($this, 'remove'), 10);
            remove_action('delete_post', array($this, 'remove'), 10);
            remove_action('delete_post_'.$this->_type, array($this, 'remove'), 10);
            remove_action('wp_delete_post', array($this, 'remove'), 10);
            remove_action('deleted_post', array($this, 'remove'), 10);
        }
        public function add($postId, $post = '')
        {
            $posts = processPostId($postId, $post);
            $success = array();
            foreach ($posts as $p) {
                //@codingStandardsIgnoreStart
                if (is_a($p->post, 'WP_Post')) {
                    if ($p->post->post_type !== $this->_type) {
                        //@codingStandardsIgnoreEnd
                        continue;
                    } else {
                        //@codingStandardsIgnoreStart
                        $slug = $p->post->post_name;
                        //@codingStandardsIgnoreEnd
                        if ($slug !== '') {
                            $args = '';
                            if ($this->_hierachical) {
                                //@codingStandardsIgnoreStart
                                if (isset($p->post->post_parent) && $p->post->post_parent !== 0) {
                                    $parentPost = get_post($p->post->post_parent);
                                    $parentSlug = $parentPost->$post_name;
                                    //@codingStandardsIgnoreEnd
                                    $parentId = IM_Taxonomy::getID($parentSlug);
                                    if ($parentId !== 0) {
                                        $args = (is_array($args)) ? $args : array();
                                        $args['parent'] = $parentId;
                                    }
                                }
                            }
                            $success[] = $this->createTerm($slug, $args);
                        }
                    }
                }
            }
            if (count($success) === 1) {
                return $success[0];
            } else if (count($success) > 0) {
                foreach ($success as $s) {
                    if ($s === 0) {
                        return false;
                    }
                }
                return true;
            } else {
                return;
            }
        }
        public function remove($postId, $post)
        {
            $posts = processPostId($postId, $post);
            foreach ($posts as $p) {
                $reset = false;
                if ($p->post === '' || !is_a($p->post, 'WP_Post')) {
                    $reset = true;
                    $p->post = get_post($p->id);
                }
                //@codingStandardsIgnoreStart
                if (is_a($p->post, 'WP_Post') && $p->post->post_type === $this->_type) {
                    $slug = $p->post->post_name;
                    //@codingStandardsIgnoreEnd
                    if ($slug !== '') {
                        $this->deleteTerm($slug);
                    }
                }
                if ($reset) {
                    wp_reset_postdata();
                }
            }
        }
        public function destroy()
        {
            $this->disable();
            $all = $this->getTermNames();
            foreach ($all as $a) {
                $this->deleteTerm($a);
            }
        }
        public function getTerms()
        {
            $a = get_terms(
                array(
                    'taxonomy' => $this->_taxonomy,
                    'hide_empty' => false
                )
            );
            return (is_a($a, 'WP_Error')) ? array() : $a;
        }
        public function getTermNames()
        {
            $a = array();
            $terms = $this->getTerms();
            foreach ($terms as $t) {
                $a[] = $t->name;
            }
            return $a;
        }
        public function getTermIds()
        {
            $a = array();
            $terms = $this->getTerms();
            foreach ($terms as $t) {
                $a[] = $t->id;
            }
            return $a;
        }
    }
}
