<?php
if (!class_exists('ArgumentHandler')) {
    include_once('ArgumentHandler.php');
}
if (!class_exists('FontFace')) {
    class FontFace
    {
        public $title = '';
        public $scoped = false;
        public $selector = '*';
        public $weights = [];
        public $styles = [];
        private $_files = [];
        public function __construct($args)
        {
            $acceptedArgs = array(
                'style' => '',
                'weight' => '',
                'files' => '',
                'title' => '',
                'scoped' => '',
                'selector' => ''
            );
            $ah = new ArgumentHandler($args);
            $p = $ah->passArguments($args);
            if (isset($p['style']) && !empty($p['style'])) {
                $this->setStyles($p['style']);
            }
            if (isset($p['weight']) && !empty($p['weight'])) {
                $this->setWeights($p['weight']);
            }
            if (isset($p['title']) && !empty($p['title'])) {
                $this->setTitle($p['title']);
            }
            if (isset($p['selector']) && !empty($p['selector'])) {
                $this->setSelector($p['selector']);
            }
            if (isset($p['scoped']) && !empty($p['scoped'])) {
                $this->setScope($p['scoped']);
            }
            if (isset($p['files']) && !empty($p['files'])) {
                $this->add($p['files']);
            }
        }
        public function setStyles($styles = '')
        {
            $styles = (is_string($styles)) ? preg_split('/[\s,]+/', $styles) : $styles;
            if (is_array($styles) && count($styles) > 0) {
                $reg = '/^normal|italic|oblique$/';
                $count = 0;
                foreach ($styles as $v) {
                    if (preg_match($reg, $v) && !in_array($v, $this->styles)) {
                        $this->styles[] = $v;
                        $count += 1;
                    }
                }
                return $count;
            }
            return false;
        }
        public function setWeights($weights = '')
        {
            $weights = (is_string($weights)) ? preg_split('/[\s,]+/', $weights) : $weights;
            if (is_array($weights) && count($weights) > 0) {
                $reg = '/^normal|bold|[1-9]00$/';
                $count = 0;
                foreach ($weights as $v) {
                    if (preg_match($reg, $v) && !in_array($v, $this->weights)) {
                        $this->weights[] = $v;
                        $count += 1;
                    }
                }
                return $count;
            }
            return false;
        }
        public function setScoped($boo = false)
        {
            $this->scoped = (is_bool($boo)) ? $boo : false;
            return ($this->scoped === $boo);
        }
        public function setTitle($str = '')
        {
            $this->title = (is_string($str) && !empty($str) && (strpos($str, "'") === false)) ? $str : $this->title;
            return ($this->title === $str);
        }
        public function setSelector($str = '')
        {
            $this->selector = (is_string($str) && !empty($str)) ? $str : $this->selector;
            return ($this->selector === $str);
        }
        public function add($arr = array())
        {
            $first = count($this->_files);
            if (is_string($arr)) {
                $arr = preg_split('/[\s,]+/', $arr);
            }
            if (is_array($arr) && (count($arr) > 0)) {
                foreach ($arr as $k => $v) {
                    $this->_addFile($v);
                }
            }
            return (count($this->_files) - $first);
        }
        private function _addFile($path)
        {
            if (is_string($path) && !empty($path)) {
                $good = array('eot', 'svg', 'ttf', 'otf', 'woff', 'woff2');
                $f = new FontFaceFile($path);
                if ($f->valid && in_array($f->extension, $good)) {
                    $can = true;
                    foreach ($this->_files as $k => $v) {
                        if ($v->extension === $f->extension) {
                            $can = false;
                            break;
                        }
                    }
                    if ($can) {
                        $this->_files[] = $f;
                        return true;
                    }
                }
            }
            return false;
        }
        public function valid()
        {
            return (!empty($this->title) && $this->hasFiles());
        }
        public function hasFiles()
        {
            return (count($this->_files) > 0);
        }
        public function findEOTFile()
        {
            foreach ($this->_files as $k => $v) {
                if ($v->extension === 'eot') {
                    return $v;
                }
            }
            return false;
        }
        public function hasEOTFile()
        {
            return ($this->findEOTFile() === false) ? false : true;
        }
        public function view()
        {
            return $this->viewStyleTag(true);
        }
        public function viewEOTCSS()
        {
            $str = '';
            if ($this->hasEOTFile()) {
                $f = $this->findEOTFile();
                $str .= "src: url('".$f->path."');\n";
                $str .= "src: url('".$f->path."?#eotfix') format('".$f->format."')";
            }
            return $str;
        }
        public function viewSelectorDefinition()
        {
            $str = '';
            if (!empty($this->selector) && !empty($this->title)) {
                return "\n".$this->selector.' {
        font-family: \''.$this->title.'\' !important;
    }';
            }
            return $str;
        }
        public function viewAtDefinition()
        {
            $str = '';
            if (count($this->_files) > 0 && !empty($this->title)) {
                $str = '@font-face {
        font-family: \''.$this->title."';\n";
                $str .= (count($this->weights) > 0) ? 'font-weight: '.explode($this->weights, ', ').";\n" : '';
                $str .= (count($this->styles) > 0) ? 'font-style: '.explode($this->styles, ', ').";\n" : '';
                $srcs = '';
                if ($this->hasEOTFile()) {
                    $srcs .= $this->viewEOTCSS();
                }
                foreach ($this->_files as $k => $v) {
                    if ($v->extension !== 'eot') {
                        $srcs .= (empty($srcs)) ? 'src: ' : ", \n";
                        $srcs .= "url('".$v->path."') format('".$v->format."')";
                    }
                }
                $str .= $srcs;
            }
            return $str;
        }
        public function viewStyleTag($includeSelector = false)
        {
            $includeSelector = (is_bool($includeSelector)) ? $includeSelector : true;
            $str = '';
            $str .= $this->viewAtDefinition();
            if (!empty($str)) {
                $str = '<style type="text/css"'.(($this->scoped) ? ' scoped' : '').'>
        '.$str.';
    }'.(($includeSelector) ? $this->viewSelectorDefinition() : '').'
    </style>';
            }
            return $str;
        }
    }
}
if (!class_exists('FontFaceFile')) {
    class FontFaceFile
    {
        public $basename = '';
        public $extension = '';
        public $path = '';
        public $mime = '';
        public $valid = false;
        public $format = '';
        public function __construct($filePath)
        {
            if (is_string($filePath) && !empty($filePath)) {
                $path = pathinfo($filePath);
                $this->basename = (isset($path['basename'])) ? $path['basename'] : $this->basename;
                //@codingStandardsIgnoreStart - line-length;
                $this->extension = (isset($path['extension'])) ? strtolower($path['extension']) : strtolower($this->extension);
                //@codingStandardsIgnoreEnd
                if (!empty($this->basename)) {
                    $this->path = $filePath;
                    $this->getMimeType($this->extension);
                    $this->getFormat($this->extension);
                    if (!empty($this->mime)) {
                        $this->valid = true;
                    }
                }
            }
        }
        public function getFormat($str = '')
        {
            $str = (is_string($str) && !empty($str)) ? $str : $this->extension;
            $form = '';
            if (!empty($str)) {
                switch ($str) {
                    case 'eot':
                        $form = 'embedded-opentype';
                        break;
                    case 'ttf':
                        $form = 'truetype';
                        break;
                    case 'otf':
                        $form = 'opentype';
                        break;
                    case 'woff':
                        $form = 'woff';
                        break;
                    case 'woff2':
                        $form = 'woff2';
                        break;
                    case 'svg':
                        $form = 'svg';
                        break;
                }
            }
            $this->format = $form;
            return $this->format;
        }
        public function getMimeType($str = '')
        {
            $str = (is_string($str) && !empty($str)) ? $str : $this->extension;
            if (!empty($str)) {
                if (function_exists('wp_get_allowed_mime_types')) {
                    $mimes = wp_get_allowed_mime_types();
                    foreach ($mimes as $type => $mime) {
                        $reg = '/^'.$type.'$/';
                        if (preg_match($reg, $str)) {
                            $this->mime = $mime;
                            return $mime;
                        }
                    }
                } else {
                    //hard-coded mimetypes;
                    $mime = '';
                    switch ($str) {
                        case 'eot':
                            $mime = 'application/vnd.ms-fontobject';
                            break;
                        case 'woff':
                            $mime = 'application/font-woff';
                            break;
                        case 'woff2':
                            $mime = 'font/woff2';
                            break;
                        case 'ttf':
                            $mime = 'application/x-font-truetype';
                            break;
                        case 'otf':
                            $mime = 'application/x-font-opentype';
                            break;
                        case 'svg':
                            $mime = 'image/svg+xml';
                            break;
                    }
                    if (!empty($mime)) {
                        $this->mime = $mime;
                    }
                    return $mime;
                }
            }
            return '';
        }
    }
}
