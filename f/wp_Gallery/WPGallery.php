<?php
if (!class_exists('WPGallery')) {
    /**
     * @module WPGallery
     * @submodule WPGallery
     * @description 
     * @dependencies ArgumentHandler (included on main)
     **/
    class WPGallery 
    {
        private $_name = '';
        private $_definition = false;
        private $_posts = '';
        private $_foundPosts = -1;
        private $_offset = 0;

        private $_hasPreview = false;
        private $_previewId = false;
        private $_requestedPreviewId = false;
        
        private $_definitionVars = array();
        private $_viewInstance = false;
        private $_viewPreview = false;
        private $_view = false;
        private static $_currentId = 0;
        private $_valid = false;

        private $_viewOpt = true;

        public function __construct($args = array())
        {
            $defaultArgs = array(
                'name' => '',
                'definition' => '',
                'numberPerPage' => '', //this should be in the definition not the gallery
                'definitionVars' => '',
                'hasPreview' => '',
                'viewOptions' => ''
            );
            $a = new ArgumentHandler($defaultArgs);
            $p = $a->passArguments($args);
            if (isset($p['name'])) {
                $this->setName($p['name']);
            } else { 
                $n = WPGalleryPlugin::getName();
                $this->setName($n);
            }
            if (!isset($p['definition']) && WPGalleryPlugin::getDefinition($this->_name) !== false) {
                $p['definition'] = WPGalleryPlugin::getDefinition($this->_name);
            }
            if (isset($p['definition'])) {
                $n = $p['definition'];
                if (is_string($n) && !empty($n)  && WPGalleryPlugin::isDefinitionDeclared($n)) {
                    $this->_definition = WPGalleryPlugin::findDefinition($n);
                }
            }
            if (isset($p['definitionVars'])) {
                $a = (is_array($p['definitionVars'])) ? $p['definitionVars'] : array($p['definitionVars']);
                $this->setDefinitionVars($a);
            }
            $vo = (isset($p['viewOptions'])) ? $p['viewOptions'] : array();
            $this->setViewOptions($vo);

            $this->_hasPreview = (isset($p['hasPreview']) && is_bool($p['hasPreview'])) ? $p['hasPreview'] : false;
            if ($this->_name !== '' && $this->_definition !== false) {
                $this->_valid = true;
                WPGalleryPlugin::addGallery($this);
            }
        }
        public function isValid()
        {
            return $this->_valid;
        }
        public function hasPreview()
        {
            return $this->_hasPreview;
        }
        public function produceViewOptArray()
        {
            return array(
                'next' => true,
                'previous' => true,
                'pages' => true,
                'any' => true
            );
        }
        public function setViewOptions($arr) 
        {
            $before = $this->produceViewOptArray();
            $after = $this->produceViewOptArray();

            $arr = (is_array($arr)) ? $arr : array($arr);
            foreach ($arr as $k => $v) {
                if (empty($k)) {
                    $k = $v;
                    $v = true;
                }
            }
            $this->_viewOpt = array(
                'before' => $before,
                'after' => $after
            );
            //this needs sorting;
        }
        /**
         * @method getDefinitionVars
         * @description
         * @return {Array} With key->value pairs of Definition Vars
         * @notes   initially used array_merge for joining but 
         *          didn’t work because of empty values overwriting _definitionVars
         **/
        public function getDefinitionVars()
        {
            $arr = WPGalleryDefinition::retrieveDefinitionVars($this->_name);
            foreach ($this->_definitionVars as $k => $v) {
                if (key_exists($k, $arr)) {
                    $arr[$k] = (empty($arr[$k])) ? $v : $arr[$k];
                } else {
                    $arr[$k] = $v;
                }
            }
            return $arr;
        }
        /**
         * @method getPosts
         * @description either used cached posts from instance private var _posts, or produces and runs query
         * @notes dislike the reliance on cached value and single-use but does not seem to be causing issues
         **/
        public function getPosts($pg = 0)
        {
            if (!empty($this->_posts)) {
                return $this->_posts;
            } else {
                $wpq = new WP_Query($this->_definition->produceArgs($this));
                // phpcs:disable
                $this->_foundPosts = $wpq->found_posts;
                // phpcs:enable
                $this->_posts = $wpq;
                return $this->_posts;
            }
        }
        /**
         * @method produceGalleryLink
         * @param $arr {Array}
         * @returns {String} url of gallery
         * @notes the [ and ] symbols are being caught on the encoding and then messed up
         **/
        public function produceGalleryLink($arr = array())
        {
            $url = false; //could potentially supply a url;
            $url = (WPGalleryPlugin::isAjax()) ? '': false;
            $url = remove_query_arg($this->removeGetVars(), $url);
            $url = esc_url(add_query_arg($this->produceGetVars($arr), $url)); //you need to do something about the existing gets
            $url = str_replace('%5B', '[', $url); //put [ back
            $url = str_replace('%5D', ']', $url); //put ] back
            return $url;
        }
        /**
         * @method removeGetVars
         * @returns {Array} containing keys to be removed, combining custom definition vars and gallery vars
         * @notes name is confusing produces an array of GetVars for removal for a clean url free of gallery stuff;
         **/
        public function removeGetVars()
        {
            $vars = array();
            $vars[] = WPGalleryPlugin::$gkGalleryName;
            $vars[] = WPGalleryPlugin::$gkGalleryPreview;
            $vars[] = WPGalleryPlugin::$gkGalleryOffset;
            $vars[] = WPGalleryPlugin::$gkGalleryNumberToRetrieve;

            $keylist = $this->getDefinitionVars();
            foreach ($keylist as $k => $v) {
                $vars[] = $k;
            }
            
            return $vars;
        }
        
        public function produceGetVars($args = array())
        {
            $defaultArgs = array(
                'offset' => '',
                'page' => '',
                'number' => '',
                'preview' => ''
            );
            $ah = new ArgumentHandler($defaultArgs);
            $p = $ah->passArguments($args); //p is used for default 4 args, may be able to add addtional

            $vars = array();
            $vars[WPGalleryPlugin::$gkGalleryName] = $this->getName();
            $vars[WPGalleryPlugin::$gkGalleryPreview] = $this->getPreviewId();
            //$vars[WPGalleryPlugin::$gkGalleryNumberToRetrieve] = $this->getNumberToDisplay();
            $vars[WPGalleryPlugin::$gkGalleryPage] = $this->getCurrentPage($this);
            $vars[WPGalleryPlugin::$gkGalleryOffset] = $this->getOffset($this);
            //all above are overridden by inline requests;
            $vars = array_merge($this->getDefinitionVars(), $vars); //allows DefinitionVars to over-write
            foreach ($p as $k => $v) {
                $p[$k] = (is_numeric($v) && (int) $v == $v && $v > 0) ? $v : false;
            }
            $offsetDone = false;
            $pageDone = false;
            if (isset($p['offset']) && $p['offset'] !== false) { //offset takes presidence over page
                $offsetDone = true;
                $vars[WPGalleryPlugin::$gkGalleryOffset] = $p['offset'];
            } 
            if (isset($p['page']) && $p['page'] !== false) {
                $pageDone = true;
                $vars[WPGalleryPlugin::$gkGalleryPage] = $p['page'];
            }
            if (isset($p['number']) && $p['number'] !== false) {
                $vars[WPGalleryPlugin::$gkGalleryNumberToRetrieve] = $p['number'];
            }
            if (isset($p['preview'])) {
                $vars[WPGalleryPlugin::$gkGalleryPreview] = $p['preview'];
            }
            if (isset($vars[WPGalleryPlugin::$gkGalleryPreview]) && $vars[WPGalleryPlugin::$gkGalleryPreview] == -1) {
                unset($vars[WPGalleryPlugin::$gkGalleryPreview]);
            }
            if (isset($vars[WPGalleryPlugin::$gkGalleryOffset]) && isset($vars[WPGalleryPlugin::$gkGalleryPage])) {
                if ($pageDone && !$offsetDone) {
                    unset($vars[WPGalleryPlugin::$gkGalleryOffset]);
                } else {
                    unset($vars[WPGalleryPlugin::$gkGalleryPage]);
                }
            }
            return $vars;
        }
        /**
         * @method getTotalNumberOfPosts
         * @returns {Number} Found Posts for Gallery
         **/
        public function getTotalNumberOfPosts()
        {
            if ($this->_foundPosts === -1) {
                $this->getPosts();
                return $this->_foundPosts;
            } else {
                return $this->_foundPosts;
            }
        }
        public function getNumberPerPage()
        {
            return $this->_definition->getNumberPerPage();
        }
        public function produceContextArgs($pg = 0)
        {
            $arr = array();
            $arr['posts_per_page'] = $this->getNumberPerPage();
            //if ($pg !== 0) {
            $arr['offset'] = $this->getOffset($pg);
            //}
            return $arr;
        }
        public function getPageForOffset($off = -1)
        {
            $off = (is_numeric($off) && (int) $off == $off && $off > -1) ? $off : -1;
            $off = ($off === -1) ? $this->getOffset() : $off;
            return floor($this->getNumberPerPage()/$off);
        }
        public function getOffset($off = -1)
        {
            $off = WPGalleryPlugin::getCurrentOffsetForGallery($this->getName());
            if ($off === false) {
                $off = $this->_offset;
            }
            return $off;
        }
        public function setDefinitionVars($vars)
        {
            $this->_definitionVars = (is_array($vars)) ? $vars : $this->_definitionVars;
        }
        public function viewPreview($id = false)
        {
            $str = '';
            if ($this->hasPreview()) {
                $id = $this->getPreviewId($id);
                if ($id !== false) {
                    if ($this->_viewPreview !== false && function_exists($this->_viewPreview)) {
                        $str .= call_user_func($this->_viewPreview, $id);
                    } else {
                        $str .= '<section class="preview">';
                        //preview
                        $str .= '</section>';
                    }
                }
            }
            return $str;
        }
        public function viewPageNavigation()
        {
            $str = '<ol>';
                for ($i = 1; $i < ($this->getNumberOfPages() + 1); $i += 1) {
                    if ($i != ($this->getCurrentPage() + 1)) {
                        $str .= '<li><a href="'.$this->getPageLink($i).'">'.$i.'</a></li>';
                    } else {
                        $str .= '<li>'.$i.'</li>';
                    }
                }
            $str .= '</ol>';
            return $str;
        }
        public function needsControls()
        {
            return ($this->hasPrevious() || $this->hasNext() || $this->getNumberOfPages() > 2) ? true : false;
        }
        public function viewBeforeControls()
        {
            return $this->viewControls('before');
        }
        public function viewAfterControls()
        {
            return $this->viewControls('after');
        }
        public function viewControls($location = 'both')
        {
            $view = array(
                'any' => true,
                'next' => true,
                'previous' => true,
                'pages' => true
            );
            $str = '';
            switch ($location) {
                case 'before':
                    $view = $this->_viewOpt['before'];
                    break;
                case 'after':
                    $view = $this->_viewOpt['after'];
                    break;
            }
            if ($view['any'] && $this->needsControls()) {
                $str .= '<nav class="gallerynav">';
                if (($view['next'] && $this->hasNext()) || ($view['previous'] && $this->hasPrevious())) {
                    $str .= '<ol>';
                        // @codingStandardsIgnoreStart line-length
                        $str .= ($this->hasPrevious() && $view['previous']) ? '<li><a href="'.$this->getPreviousLink().'" class="gallery-previous">Previous</a></li>' : '';
                        $str .= ($this->hasNext() && $view['next']) ? '<li><a href="'.$this->getNextLink().'" class="gallery-next">Next</a></li>' : '';
                        // @codingStandardsIgnoreEnd line-length
                    $str .= '</ol>';
                }
                if ($view['pages'] && ($this->getNumberOfPages() > 2)) {
                    $str .= $this->viewPageNavigation();
                }
                $str .= '</nav>';
            }
            return $str;
        }
        public function view($definitionVars = false)
        {
            if ($this->isValid()) {
                global $post;
                if ($definitionVars !== false) {
                    $this->setDefinitionVars($definitionVars);
                }
                $str = '';
                if ($this->getTotalNumberOfPosts() > 0) {
                    if ($this->_view !== false && function_exists($this->_view)) {
                        call_user_func($this->_view, $this);
                    } else {
                        if (WPGalleryPlugin::getNumberToRetrieve($this->getName()) === false) {
                            if (WPGalleryPlugin::shouldView('gallery')) {
                                $str = '<section class="wpgallery" id="'.$this->_name.'"';
                                $str .= ' data-definition="'.$this->_definition->getName().'"';
                                $str .= ' data-definition-vars="'.$this->getDefinitionVars().'">';
                            }
                            $str .= (WPGalleryPlugin::shouldView('preview')) ? $this->viewPreview() : '';
                            $str .= (WPGalleryPlugin::shouldView('inner')) ? '<div class="inner">' : '';
                            $str .= (WPGalleryPlugin::shouldView('navigation')) ? $this->viewBeforeControls() : '';
                            if (WPGalleryPlugin::shouldView('thumbs')) {
                                $str .= '<ol class="thumbs">';
                                    $str .= $this->viewInstanceLoop();
                                $str .= '</ol>';
                            }
                            $str .= (WPGalleryPlugin::shouldView('navigation')) ? $this->viewAfterControls() : '';
                            $str .= (WPGalleryPlugin::shouldView('inner')) ? '</div>' : '';
                            $str .= (WPGalleryPlugin::shouldView('gallery')) ? '</section>' : '';
                        } else {
                            //for defined number of returns—assumes ajax;
                            $str = $this->viewInstanceloop();
                        }
                    }
                    wp_reset_postdata();
                }
                return $str;
            } else {
                $errorMessage = 'WPGallery->view() attempted without proper definition declaration for '.$this->_name;
                trigger_error($errorMessage);
                return '';
            }
        }
        public function viewInstanceLoop()
        {
            $str = '';
            $posts = $this->getPosts();
            while ($posts->have_posts()) {
                $posts->the_post();
                $str .= $this->viewInstance($posts->post);
            }
            $this->_posts = ''; //using to try to enable multi-used
            return $str;
        }
        public function viewInstance($post, $isPreview = '')
        {
            $s = '';
            $isPreview = (empty($isPreview) || !is_bool($isPreview)) ? $this->isPreviewId($post->ID) : $isPreview;
            if ($this->_viewInstance !== false) {
                $s = call_user_func($this->_viewInstance, $post, $isPreview);
            } else {
                $s .= '<li'.(($isPreview) ? ' class="previewed"' : '').'>';
                    $url = $this->produceGalleryLink(
                        array(
                            'preview' => $post->ID
                        )
                    );
                    //$s .= '<a href="'.$url.'" id="gall'.$post->ID.'">';
                    $fn = wp_get_attachment_image_src($post->ID, 'full')[0];
                    $m = new MultiSrcImage();
                    $m->setLink($url);
                    $m->setLinkAttrs(array('id' => 'gall'.$post->ID));
                    $m->setWordpressId($post->ID);
                    $m->setDetailsFromWordpress($post->ID);
                    $m->addFiles($fn);
                    $m->wordpressFindSizes($fn);
                    $s .= $m->view();
                    //$s .= '</a></li>';
                    $s .= '</li>';
            }
            return (is_string($s)) ? $s : '';
        }
        public function setPreviewId($id)
        {
            $id = (is_numeric($id) && (int) $id == $id && $id > 0) ? $id : false;
            if ($id !== false) {
                //need a proper check here;
                $this->_requestedPreviewId = $id;
                return true;
            } else {
                return false;
            }
        }
        public function isPreviewId($id)
        {
            return ($this->getPreviewId() == $id);
        }
        public function fallbackPreviewId()
        {
            if ($this->_fallbackPreviewId !== false) {
                //need a proper fallback here;
                $this->_fallbackPreviewId = 1;
            }
            return $this->_fallbackPreviewId;
        }
        public function getPreviewId()
        {
            if ($this->_previewId === false) {
                $this->_previewId = WPGalleryPlugin::getPreviewId($this->getName());
                if ($this->_previewId  === false) {
                    if ($this->_hasPreview) {
                        // phpcs:disable
                        $this->_previewId = ($this->_requestedPreviewId !== false) ? $this->_requestedPreviewId : $this->fallbackPreviewId();
                        // phpcs:enable
                    } else {
                        $this->_previewId = -1; //can be anything as doesn’t need to be valid;
                    }
                }
            }
            return $this->_previewId;
        }
        public function getCurrentPage()
        {
            return floor($this->getOffset() / $this->getNumberPerPage()) + 1;
        }
        public function getRequestedPage() //alias for getCurrentPage
        {
            return $this->getCurrentPage();
        }
        public function canBuild()
        {
            return (!$this->_built && !WPGallery::initialised) ? true : false; 
        }
        public function hasPrevious()
        {
            return ($this->getOffset() > 0) ? true : false;
        }
        public function getNumberOfPages()
        {
            return ceil($this->getTotalNumberOfPosts()/$this->getNumberPerPage());
        }
        public function getCurrentOnPage()
        {
            return ceil(($this->_currentIndex - 1)/$this->getNumberPerPage());
        }
        public function currentFirstShown()
        {
            return $this->getOffset();
        }
        public function currentLastShown()
        {
            $last = ($this->getOffset() + $this->getNumberPerPage());
            $last = ($last > $this->getTotalNumberOfPosts()) ? $this->getTotalNumberOfPosts() : $last;
            return $last;
        }
        public function hasNext()
        {
            return ($this->currentLastShown() < $this->getTotalNumberOfPosts()) ? true : false;
        }
        public static function getCurrentId()
        {
            return self::$_currentId;
        }
        public function getPageLink($i)
        {
            $i = (is_numeric($i) && (int) $i == $i && $i > 0) ? $i : 0;
            $i = ($i > $this->getNumberOfPages()) ? $this->getNumberOfPages() : $i;
            return $this->produceGalleryLink(
                array(
                    'page' => $i
                )
            );
        }
        public function getPreviousLink()
        {
            if ($this->hasPrevious()) {
                return $this->produceGalleryLink(
                    array(
                        'page' => $this->getCurrentPage() - 1
                    )
                );
            }
        }
        public function getNextLink()
        {
            if ($this->hasNext()) {
                return $this->produceGalleryLink(
                    array(
                        'page' => $this->getCurrentPage() + 1
                    )
                );
            }
        }
        public function getName()
        {
            return $this->_name;
        }
        private function setName($name = '')
        {
            //reg exp for gallery name
            if (is_string($name) && !empty($name) && !WPGalleryPlugin::isGalleryDeclared($name)) {
                $this->_name = $name;
            } else {
                self::$_currentId += 1;
                $this->_name = 'gall'.self::getCurrentId();
            }
        }
        public function setView($func)
        {
            if (is_string($func) && function_exists($func)) {
                $this->_view = $func;
                return true;
            }
            return false;
        }
        public function setViewInstance($func)
        {
            if (is_string($func) && function_exists($func)) {
                $this->_viewInstance = $func;
                return true;
            }
            return false;
        }
        public function setViewPreview($func)
        {
            if (is_string($func) && function_exists($func)) {
                $this->_viewPreview = $func;
                return true;
            }
            return false;
        }
    }
}
