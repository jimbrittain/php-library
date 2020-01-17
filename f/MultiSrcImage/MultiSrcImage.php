<?php
if (!class_exists('ArgumentHandler')) {
    require_once('ArgumentHandler.php');
}
if (!class_exists('MultiSrcImageView')) {
    require_once('MultiSrcImageView.php');
}
if (!function_exists('hasResponsiveInFilename')) {
    require_once('hasResponsiveInFilename.php');
}
/*
 * Currently additonal attributes not implemented.
 * To call, construct with
 *  array
 *      title = which is alt
 *      files = if you already have the file list
 *      filename = if you want to restrict your 
 *          filelist based on a filename prefix e.g. only 'morris' files 'morris_1x.png' etc
 *      attributes = not implemented yet
 *      customOrder = array('jpg', 'png', 'webp') where webp first, png next jpg = last and fallback
 * Then addFiles(array/string) if needed
 * Then view to see either picture or image
 **/
if (!class_exists('MultiSrcImage')) {
    /*
        @class MultiSrcImage
        @module MultiSrcImage
        @constructor
        @description - used to create a multi-source image
    */
    class MultiSrcImage
    {
        private static $_mid = 1;
        public $images = array();
        private $_id = '';
//        public $title = '';
        public $filename = '';
        public $link = '';
        public $linkAttrs = array();
        private $_filenameConfidence = true;
        private $_confidentFilename = '';
        private $_wordpressId = -1;
        public $metas = array();
        public $customOrder = array();
        public $additionalAttr = '';
        public static $globalOrderVar = 'multiSrcOrderValue';
        private $_order = array(
            'gif', 'jpg', 'png', 'webp', 'jp2', 'jpx', 'jxr', 'tiff', 'svg'
        );
        /*
            @method __construct
            @param $args
                title
                files
        */
        public function __construct($args = array())
        {
            $this->_id = 'msi'.self::$_mid;
            self::$_mid += 1;
            $acceptedArgs = array(
                'title' => '',
                'filename' => '',
                'files' => '',
                'order' => '',
                'attributes' => '',
                'metas' => ''
            );
            $ah = new ArgumentHandler($acceptedArgs);
            $p = $ah->passArguments($args);
            if (isset($p['title'])) {
                $this->setTitle($p['title']);
            }
            if (isset($p['filename'])) {
                $this->setFileName($p['filename']);
            }
            if (isset($p['files'])) {
                $this->addFiles($p['files']);
            }
            if (isset($p['order'])) {
                $this->setCustomOrder($p['order']);
            }
            if (isset($p['attributes'])) {
                $this->setAttributes($p['attributes']);
            }
            if (isset($p['metas'])) {
                $this->setMetas($p['metas']);
            }
        }
        /* order is backwards, e.g. jpg, png, webp would order webp, png, jpg */
        public static function imagePreference(MultiSrcImageReference $a, MultiSrcImageReference $b)
        {
            $av = array_search($a->type, $GLOBALS[MultiSrcImage::$globalOrderVar]);
            $bv = array_search($b->type, $GLOBALS[MultiSrcImage::$globalOrderVar]);
            $av = ($av === false) ? count($GLOBALS[MultiSrcImage::$globalOrderVar]) : $av;
            $bv = ($bv === false) ? count($GLOBALS[MultiSrcImage::$globalOrderVar]) : $bv;
            if ($av == $bv) {
                return 0; 
            } 
            return ($av > $bv) ? -1 : 1;
        }
        /* order is forwards e.g. default at front of array */
        public static function variantPreference(MultiSrcImageReference $a, MultiSrcImageReference $b)
        {
            $av = $a->assumedWidth;
            $bv = $b->assumedWidth;
            if ($av == $bv) {
                return 0;
            }
            return ($av == $bv) ? 1 : -1;
        }
        public function id()
        {
            return $this->_id;
        }
        /**
            @method wpGetDetails
            @param $key {String}
            @return {Array||String}
         **/
        private function _wpGetDetails($key = '')
        {
            if (function_exists('get_post_meta') && $this->_wordpressId !== -1) {
                $r = array();
                $r['title'] = get_the_title($this->_wordpressId);
                $r['caption'] = get_the_excerpt($this->_wordpressId);
                $r['description'] = get_the_content($this->_wordpressId);
                $r['alt'] = get_post_meta($this->_wordpressId, '_wp_attachment_image_alt');
                $r = array_merge(get_post_meta($this->_wordpressId), $r);
                if (!empty($key) && is_string($key) && isset($r[$key])) {
                    return $r[$key];
                }
                return $r;
            }
            return array();
        }
        public function setDetailsFromWordpress()
        {
            $details = $this->_wpGetDetails();
            foreach ($details as $key => $value) {
                switch ($key) {
                    case 'title':
                        $this->setTitle($value);
                        break;
                    case 'alt':
                        $this->setAlt($value);
                        break;
                    case 'description':
                        $this->setDescription($value);
                        break;
                    default:
                        $this->metas[$key] = $value;
                        break;
                }
            }
        }
        public function setWordpressId($id = -1)
        {
            if ($id !== -1 && is_numeric($id) && $id > -1 && (int) $id == $id) {
                $this->_wordpressId = $id;
                return true;
            } else {
                return false;
            }
        }
        public function setLinkAttrs($keys, $value = null)
        {
            $keys = (is_string($keys) && !empty($keys)) ? array($key => $value) : $keys;
            $count = 0;
            if (is_array($keys)) {
                foreach ($keys as $key => $value) {
                    if (is_string($key) && !empty($key)) { //could also add valid attr check/preg_match
                        $this->linkAttrs[$key] = $value;
                        $count += 1;
                    }
                }
            }
            return ($count > 0) ? true : false;
        }
        public function set(String $key, $str = '')
        {
            if (is_string($str) && !empty($str)) {
                switch ($str) {
                    case 'link':
                        return $this->setLink($str);
                    default:
                        $this->metas[$key] = $str;
                        return true;
                }
            }
            return false;
        }
        public function setLink($str = '')
        {
            $this->link = $str;
            return true;
        }
        public function setTitle($str = '')
        {
            return $this->set('title', $str);
        }
        public function setAlt($str = '')
        {
            return $this->set('alt', $str);
        }
        public function setDescription($str = '')
        {
            return $this->set('description', $str);
        }
        public function setLongdesc($str = '')
        {
            return $this->set('description', $str);
        }
        public function setFileName($str = '')
        {
            if (is_string($str) && !empty($str)) {
                $this->filename = $str;
                $this->_filenameConfidence = (hasResponsiveInFilename($str)) ? false : true;
                $this->_confidentFilename = getFilenameWithoutResponsiveParts($str);
                return true;
            }
            return false;
        }
        /**
        * @method setCustomOrder
        * @param $arr {Array}
        * @return {Boolean} true if set to an array otherwise false
         **/
        public function setCustomOrder($arr)
        {
            if (!empty($arr) && is_array($arr)) {
                $this->customOrder = $arr;
                return true;
            } else {
                $this->customOrder = array();
                return false;
            }
        }
        /**
        * @method tempSetOrder
        * @private
        * @description temporarily sets the GLOBALS[self::globalOrderVar] to either customOrder or default
         **/
        private function tempSetOrder()
        {
            $a = $this->customOrder;
            $GLOBALS[self::$globalOrderVar] = (!empty($a) && is_array($a)) ? $a : $this->_order;
        }
        /**
         * @method resetOrder
         * @private
         * @description - resets the GLOBALS[self::globalOrderVar] to default
         **/
        private function resetOrder()
        {
            $GLOBALS[self::$globalOrderVar] = $this->_order;
        }
        public function setAttributes($str = '')
        {
            if (!empty($str)) {
                if (is_string($str)) {
                    //pregmatch based on lower and space | lower="anything"
                } else if (is_array($str)) {
                    //if is string add to base string,
                    //if key->value check key against lower, add key="value"
                }
            } else {
                $this->additionalAttrs = '';
            }
        }
        public function addFiles($arr)
        {
            $arr = (is_string($arr)) ? preg_split('/[\s,]+/', $arr) : $arr;
            if (is_array($arr) && count($arr) > 0) {
                $count = 0;
                foreach ($arr as $path) {
                    $t = $this->_addImageFile($path);
                    $count += ($t) ? 1 : 0;
                }
                return $count;
            }
            return false;
        }
        private function _addImageFile($path = '')
        {
            if (is_string($path) && !empty($path)) {
                if (!$this->hasImagePath($path) && $this->hasFilenameInPath($path)) {
                    $m = new MultiSrcImageReference($path);
                    if (!$this->hasTypeVariant($m->type, $m->variant)) {
                        $this->images[] = $m;
                        return true;
                    }
                }
            }
            return false;
        }
        public function hasFilenameInPath($path = '')
        {
            if (empty($this->filename)) {
                return true;
            } else {
                return (strpos($path, $this->filename) !== FALSE);
            }
        }
        public function hasImagePath($path = '')
        {
            if (is_string($path) && !empty($path)) {
                foreach ($this->images as $v) {
                    if ($v->path === $path) {
                        return true;
                    }
                }
            }
            return false;
        }
        public function hasTypeVariant($type, $variant)
        {
            foreach ($this->images as $v) {
                if ($v->type === $type && $v->variant === $variant) {
                    return true;
                }
            }
            return false;
        }

        public function wordpressFindSizes($id = -1)
        {
            global $post;
            $attachId = -1;
            $id = (is_numeric($id) && (int) $id === $id && $id > 0) ? $id : $post->ID;
            if (get_post_type($id) === 'attachment') {
                $attachId = $id;
            } else {
                $attachId = get_post_thumbnail_id($id);
            }
            $this->setWordpressId($attachId);
            $attachMeta = wp_get_attachment_metadata($attachId);
            $attachSizes = array();
            $files = array();
            if (isset($attachMeta['sizes'])) {
                foreach ($attachMeta['sizes'] as $k => $v) {
                    $attachSizes[] = $k;
                }
                $attachSizes[] = 'full';
                foreach ($attachSizes as $v) {
                    $files[] = wp_get_attachment_image_src($attachId, $v)[0];
                }
            }
            if (count($files) > 0) {
                $this->addFiles($files);
                return true;
            } else {
                return false;
            }
        }
        private function _getWordpressFiles($args)
        {
            $wpq = new WP_Query($args);
            $files = array();
            if ($wpq->have_posts()) {
                while ($wpq->have_posts()) {
                    $wpq->the_post();
                    //@codingStandardsIgnoreStart - camel-case;
                    $a = wp_get_attachment_url($wpq->post->ID);
                    //@codingStandardsIgnoreEnd
                    if (!empty($a)) {
                        $files[] = $a;
                    }
                }
            }
            wp_reset_postdata();
            return $files;
        }
        public function wordpressFindVariantUrls($f = '')
        {
            if (class_exists('WP_Query')) {
                $currentFilename = $this->filename;
                $filename = $this->filename;
                $filename = (empty($filename)) ? $f : $filename;
                if ($filename !== $currentFilename) {
                    $this->setFilename($filename);
                }
                if (!empty($this->filename)) {
                    $args = array(
                        'post_type' => 'attachment',
                        'post_status' => 'inherit',
                        'meta_key' => '_wp_attached_file',
                        'meta_query' => array(
                            'relation' => 'AND',
                            'variants' => array(
                                'key' => '_wp_attached_file',
                                'value' => $this->filename,
                                'compare' => 'LIKE'
                            )
                        )
                    );
                    $files = $this->_getWordpressFiles($args);
                    if (!$this->_filenameConfidence) {
                        $args['variants']['value'] = $this->_confidentFilename;
                        $files = array_merge($files, $this->_getWordpressFiles($args));
                    }
                    if (count($files) > 0) {
                        $this->addFiles($files);
                        return true;
                    }
                }
            }
            return false;
        }
        public function allTypes()
        {
            $types = [];
            $arr = $this->images;
            $this->tempSetOrder();
            usort($arr, array('MultiSrcImage', 'imagePreference'));
            $this->resetOrder();
            foreach ($arr as $v) {
                if (!in_array($v->type, $types)) {
                    $types[] = $v->type;
                }
            }
            return $types;
        }
        public function hasMultipleTypes()
        {
            if (count($this->images) > 0) {
                return (count($this->allTypes()) === 1) ? false : true;
            }
            return false;
        }
        public function view()
        {
            return MultiSrcImageView::view($this);
        }
        public function defaultSrc($type)
        {
            $arr = $this->allOfType($type);
            $m = array_shift($arr);
            return $m->path;
        }
        /**
            @method fallbackType
            @return type used by fallback
        **/
        public function fallbackType()
        {
            $types = $this->allTypes();
            return array_pop($types);
        }
        /**
            @method allOfType
            @param type {String}
            @return return all MultiSrcReference of a type
        **/
        public function allOfType($type)
        {
            $arr = [];
            foreach ($this->images as $v) {
                if ($v->type === $type) {
                    $arr[] = $v;
                }
            }
            usort($arr, array('MultiSrcImage', 'variantPreference'));
            return $arr;
        }
        public function viewSrcsetType($type, $showDefault = true)
        {
            $str = '';
            $showDefault = (is_bool($showDefault)) ? $showDefault : true;
            $arr = $this->allOfType($type);
            if (!$showDefault) {
                array_shift($arr);
            }
            $arr = array_reverse($arr);
            foreach ($arr as $v) {
                $str .= (empty($str)) ? '' : ', ';
                $str .= $v->path.' '.$v->variant;
            }
            return $str;
        }
        public function has($str = '')
        {
            if (is_string($str) && !empty($str)) {
                switch ($str) {
                    default:
                        $r = $this->get($str);
                }
                return ($r !== '') ? true : false;
            }
            return false;
        }
        public function get($str = '', $encode = false)
        {
            if (is_string($str) && !empty($str)) {
                $encode = (is_bool($encode)) ? $encode : false;
                $r = '';
                switch ($str) {
                    case 'id':
                        $r = $this->_id;
                        break;
                    case 'link':
                        $r = $this->link;
                        break;
                    case 'linkAttrs':
                        $r = $this->linkAttrs;
                        $r = (count($r) < 1) ? '' : $r;
                        break;
                    case 'description':
                        $r = (isset($this->metas['description'])) ? $this->metas['description'] : '';
                        if (empty($r)) {
                            $r = (isset($this->metas['caption'])) ? $this->metas['caption'] : '';
                        }
                        break;
                    case 'title':
                        $r = (isset($this->metas['title'])) ? $this->metas['title'] : '';
                        if (empty($r)) {
                            $r = (isset($this->metas['alt'])) ? $this->metas['alt'] : '';
                        }
                        break;
                    case 'alt':
                        $r = (isset($this->metas['alt'])) ? $this->metas['alt'] : '';
                        if (empty($r)) {
                            $r = (isset($this->metas['title'])) ? $this->metas['title'] : '';
                        }
                        break;
                    default:
                        $r = (isset($this->metas[$str])) ? $this->metas[$str] : '';
                        break;
                }
                $r = ($encode && is_string($r)) ? urlencode($r) : $r;
                return $r;
            }
            return '';
        }

        public function getMime($type)
        {
            if (!is_string($type) || empty($type)) {
                return '';
            }
            switch ($type) {
                case 'gif':
                    $mime = 'image/gif';
                    break;
                case 'jpg':
                case 'jpeg':
                    $mime = 'image/jpeg';
                    break;
                case 'svg':
                    $mime = 'image/svg+xml';
                    break;
                case 'png':
                    $mime = 'image/png';
                    break;
                case 'bmp':
                    $mime = 'image/bmp';
                    break;
                case 'hdp':
                case 'wdp':
                case 'jxr':
                    $mime = 'image/jxr';
                    break;
                case 'jp2':
                    $mime = 'image/jp2';
                    break;
                case 'jpx':
                    $mime = 'image/jpx';
                    break;
                case 'webp':
                    $mime = 'image/webp';
                    break;
                case 'tif':
                case 'tiff':
                    $mime = 'image/tiff';
                    break;
                case 'psd':
                    $mime = 'image/vnd.adobe.photoshop';
                    break;
                default:
                    $mime = '';
                    break;
            }
            return $mime;
        }
    }
}
if (!class_exists('MultiSrcImageReference')) {
    class MultiSrcImageReference
    {
        public $path = '';
        public $mime = '';
        public $type = '';
        public $variant = '';
        public $assumedWidth = '';
        public function __construct($filepath)
        {
            $this->path = (is_string($filepath) && !empty($filepath)) ? $filepath : $this->path;
            if (!empty($this->path)) {
                $this->init();
            }
        }
        public function init()
        {
            if (!empty($this->path)) {
                $pi = pathinfo($this->path);
                if (isset($pi['extension'])) {
                    $this->setType($pi['extension']);
                }
                if (isset($pi['filename'])) {
                    $this->getVariant($pi['filename']);
                }
                return true;
            }
            return false;
        }
        public function setType($str)
        {
            $str = (is_string($str) && !empty($str)) ? strtolower($str) : '';
            if (!empty($str)) {
                switch ($str) {
                    case 'bmp':
                    case 'dib':
                        $str = 'bmp';
                        break;
                    case 'gif':
                        $str = 'gif';
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $str = 'jpg';
                        break;
                    case 'svg':
                    case 'svgz':
                        $str = 'svg';
                        break;
                    case 'png':
                        $str = 'png';
                        break;
                    case 'hdp':
                    case 'wdp':
                    case 'jxr':
                        $str = 'jxr';
                        break;
                    case 'webp':
                        $str = 'webp';
                        break;
                    case 'jp2':
                        $str = 'jp2';
                        break;
                    case 'jpx':
                        $str = 'jpx';
                        break;
                    case 'tif':
                    case 'tiff':
                        $str = 'tiff';
                        break;
                    default:
                        $str = '';
                        break;
                }
                if (!empty($str)) {
                    $this->type = $str;
                    $this->getMime($str);
                }
                return $str;
            }
            return '';
        }
        public function getMime($str)
        {
            $str = (is_string($str) && !empty($str)) ? $str : $this->type;
            switch ($str) {
                case 'gif':
                    $this->mime = 'image/gif';
                    break;
                case 'jpg':
                case 'jpeg':
                    $this->mime = 'image/jpeg';
                    break;
                case 'svg':
                    $this->mime = 'image/svg+xml';
                    break;
                case 'png':
                    $this->mime = 'image/png';
                    break;
                case 'bmp':
                    $this->mime = 'image/bmp';
                    break;
                case 'hdp':
                case 'wdp':
                case 'jxr':
                    $this->mime = 'image/jxr';
                    break;
                case 'jp2':
                    $this->mime = 'image/jp2';
                    break;
                case 'jpx':
                    $this->mime = 'image/jpx';
                    break;
                case 'webp':
                    $this->mime = 'image/webp';
                    break;
                case 'tif':
                case 'tiff':
                    $this->mime = 'image/tiff';
                    break;
                case 'psd':
                    $this->mime = 'image/vnd.adobe.photoshop';
                    break;
            }
            return $this->mime;
        }
        public function getVariant($str = '')
        {
            if (!is_string($str) || empty($str)) {
                $pi = pathinfo($this->path);
                $str = (isset($pi['filename'])) ? $pi['filename'] : $str;
            }
            if (is_string($str) && !empty($str)) {
                $str = strtolower($str);
                $matches = [];
                if (preg_match('/([0-9]+)(w)|([0-9]+)(x)$/', $str, $matches)) {
                    $this->variant = $matches[0];
                    if (isset($matches[2]) && !empty($matches[2])) {
                        $this->assumedWidth = $matches[1];
                    } else {
                        $this->assumedWidth = $matches[3] * 499;
                    }
                } else if (preg_match('/([0-9]+)x([0-9]+)$/', $str, $matches)) {
                    $this->variant = $matches[1].'w';
                    $this->assumedWidth = $matches[1];
                } else {
                    $this->variant = '1x';
                    $this->assumedWidth = 0;
                }
            }
        }
    }
}
