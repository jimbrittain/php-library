<?php
if (!class_exists('IMPostMeta')) {
    //dependencies
    if (!function_exists('getPostId')) {
        include_once('getPostId.php');
    }
    class IMPostMeta
    {
        public $name = '';
        public $slugs = [];
        public $values = [];
        public $description = '';
        public $for = '';

        private $_title = '';
        private $_nonceName = '';
        private $_storedValue = '';

        private $_inputType = 'text';
        private $_inputPattern = '';

        private $_defaultDisplay = true;

        private $_layoutArea = 'side';
        private $_layoutPriority = 'high';

        private $_defaultView = true;

        public $customInit = '';
        public $customView = '';
        public $customSave = '';
        
        function __construct($obj)
        {
            $fails = false;
            if (isset($obj['name'])) {
                $this->name = $obj['name'];
                $this->_nonceName = 'meta-box-'.$this->name;
            } else {
                $fails = true;
            }
            if (isset($obj['for'])) {
                $this->for = (is_array($obj['for'])) ? $obj['for'] : array($obj['for']);
            } else {
                $fails = true;
            }

            if (isset($obj['title']) && is_string($obj['title'])) {
                $this->_title = $obj['title'];
            }
            if (isset($obj['description']) && is_string($obj['description'])) {
                $this->description = $obj['description'];
            }
            if (isset($obj['inputType'])) {
                $this->setInput($obj['inputType'], (isset($obj['inputPattern'])) ? $obj['inputPattern'] : '');
            }
            if (isset($obj['layoutArea'])) {
                $this->setLayout($obj['layoutArea'], (isset($obj['layoutPriority'])) ? $obj['layoutPriority'] : '');
            }
            if (isset($obj['customView']) && is_callable($obj['customView'])) {
                $this->setCustomView($obj['customView']);
            }
            if (isset($obj['customInit']) && is_callable($obj['customInit'])) {
                $this->setCustomInit($obj['customInit']);
            }
            if (isset($obj['customSave']) && is_callable($obj['customSave'])) {
                $this->setCustomSave($obj['customSave']);
            }
            if (!empty($this->name) && !$fails) {
                $this->create();
            }
        }
        public function getTitle()
        {
            return (!empty($this->_title)) ? $this->_title : $this->name;
        }
        public function sanitize($v)
        {
            return filter_var($v, $this->getPHPSanitization());
        }
        public function validate($v)
        {
            if ((($this->getPHPValidation() != '') && filter_var($v, $this->getPHPValidation()))
                || ($this->getPHPValidation() == '')) {
                if (!empty($this->_inputPattern)) {
                    try {
                        if (preg_match('/'.$this->_inputPattern.'/g', $v)) {
                            return true;
                        } else {
                            return false;
                        }
                    } catch (Exception $e) {
                        return true;
                    }
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
        public function setInput($input, $pattern = '')
        {
            if (is_string($input)
                && !empty($input)
            ) {
                $done = false;
                $input = strtolower($input);
                switch ($input) {
                    case 'checkbox':
                    case 'text':
                    case 'textarea':
                    case 'date':
                    case 'email':
                    case 'url':
                    case 'number':
                        $this->_inputType = $input;
                        $done = true;
                        break;
                }
                if ($done && !empty($pattern)) {
                    $this->_inputPattern = (is_string($pattern)) ? $pattern : $this->_inputPattern;
                }
                return $done;
            }
            return false;
        }
        public function setCustomView($func = '')
        {
            if (!empty($func)
                && is_callable($func)
            ) {
                $this->customView = $func;
                $this->_defaultView = false;
                return true;
            } else {
                $this->_defaultView = true;
                $this->customView = '';
                return false;
            }
        }
        public function setCustomInit($func)
        {
            if (!empty($func)
                && is_callable($func)
            ) {
                $this->customAdminInit = $func;
                $this->_defaultDisplay = false;
                return true;
            }
            return false;
        }
        public function setLayout($context, $priority = '')
        {
            if (!empty($context)) {
                $context = is_string($context) ? strtolower($context) : $context;
                switch ($context) {
                    case 'side':
                    case 'advanced':
                    case 'normal':
                        $this->_layoutArea = $context;
                        break;
                }
            }
            if (!empty($priority)) {
                $priority = is_string($priority) ? strtolower($priority) : $priority;
                switch ($priority) {
                    case 'high':
                    case 'low':
                        $this->_layoutPriority = $priority;
                        break;
                }
            }
        }
        public function canCreateFor($postType)
        {
            if (!empty($postType) && is_string($postType)) {
                $metaOk = false;
                if (
                    function_exists('post_type_exists')
                    && post_type_exists($postType)
                ) {
                    if (
                        function_exists('post_type_supports')
                        && post_type_supports($postType, 'custom-fields')
                    ) {
                        return true;
                    }
                }
            }
            return false;
        }
        public function ensureCustomFields()
        {
            for ($i = 0; $i < count($this->for); $i += 1) {
                add_post_type_support($this->for[$i], $i);
            }
        }
        private function _createPostType($postType)
        {
            if ($this->canCreateFor($postType)) {
                add_action('add_meta_boxes_'.$postType, array($this, 'adminInit'));
                add_action('pre_post_update', array($this, 'save'), 10, 3);
                add_action('save_post', array($this, 'save'), 10, 3);
                add_action('save_post_'.$postType, array($this, 'save'), 10, 3);
                return true;
            } else {
                return false;
            }
        }
        public function create()
        {
            add_action('init', array($this, 'ensureCustomFields'));
            if (count($this->for) > 0) {
                for ($i = 0; $i < count($this->for); $i += 1) {
                    $this->_createPostType($this->for[$i]);
                }
            }
        }
        public function hasCustomInit()
        {
            if (is_string($this->customInit)
                && !empty($this->customInit)
                && function_exists($this->customInit)
            ) {
                return true;
            } else {
                return false;
            }
        }
        private function _defaultInit()
        {
            if (function_exists('add_meta_box')) {
                add_meta_box(
                    $this->getTitle(),
                    __($this->name),
                    array($this, 'edit'),
                    $this->for, 
                    $this->_layoutArea,
                    $this->_layoutPriority
                );
                return true;
            } else {
                return false;
            }
        }
        private function _customInit() 
        {
            if ($this->hasCustomInit()) {
                call_user_func($this->customInit, $this);
            } else {
                $this->_defaultInit();
            }
        }
        public function adminInit()
        {
            if ($this->hasCustomInit()) {
                $this->_customInit();
            } else {
                $this->_defaultInit();
            }
        }
        public function adminDefaultDisplay($boo)
        {
            $this->_defaultDisplay = (is_bool($boo)) ? $boo : $this->_defaultDisplay;
            return ($this->_defaultDisplay === $boo);
        }
        public function edit()
        {
            if ($this->hasCustomView()) {
                echo $this->_customView();
            } else {
                echo $this->_defaultView();
            }
        }

        public function hasCustomView()
        {
            //return (!empty($this->customView) && is_callable($this->customView)) ? true : false;
            if (is_string($this->customView)
                && !empty($this->customView)
                && function_exists($this->customView)
            ) {
                return true;
            } else {
                return false;
            }
        }
        private function _customView()
        {
            if ($this->hasCustomView()) {
                call_user_func($this->customView, $this);
            } else {
                $this->_defaultView();
            }
        }
        private function _defaultView()
        {
            $str = '<fieldset>';
            wp_nonce_field(basename(__FILE__), $this->_nonceName);
            $str = $str.'<label for="'.$this->name.'">'.$this->getTitle().'</label> ';
            $endstr = '';
            switch ($this->_inputType) {
                case 'textarea':
                    $str = $str.'<textarea id="'.$this->name.'" name="'.$this->name.'"';
                    //need to check pattern is correct for purpose, needs to include current value, check name
                    if (!empty($this->_inputPattern)) {
                        $str = $str.' pattern="'.$this->_inputPattern.'"';
                    }
                    $str = $str.'>'.$this->get();
                    $str = $str.'</textarea>';
                    break;
                default:
                    $str = $str.'<input id="'.$this->name.'" name="'.$this->name.'" type="'.$this->_inputType.'"';
                    //needs to check pattern is correct for purpose, needs to include current value, check name
                    if (!empty($this->_inputPattern)) {
                        $str = $str.' pattern="'.$this->_inputPattern.'"';
                    }
                    if ($this->_inputType === 'checkbox') {
                        $str = $str.(($this->get() === 'on') ? ' checked="checked"' : '');
                    }
                    $str = $str.' value="'.$this->get().'" />';
            }
            if (!empty($this->description)) {
                $endstr = $endstr.'<p>'.$this->description.'</p>';
            }
            return $str.$endstr.'</fieldset>';
        }
        /*
        public function get($postId = '')
        {
            if (!empty($this->_storedValue)) {
                return $this->_storedValue;
            } else {
                $postId = ($postId === '') ? getPostId($postId) : $postId;
                if ($postId !== false) {
                    $val = get_post_meta($postId, $this->name, true);
                    $val = $this->sanitize($val);
                    $val = ($this->validate($val)) ? $val : '';
                    $this->_storedValue = $val;
                    return $val;
                }
            }
        }
         */

        public function get($postId = '')
        {
            $postId = ($postId === '') ? getPostId($postId) : $postId;
            if ($postId !== false) {
                $val = get_post_meta($postId, $this->name, true);
                $val = $this->sanitize($val);
                $val = ($this->validate($val)) ? $val : '';
                $this->_storedValue = $val;
                return $val;
            } else {
                return null;
            }
        }
        public function find($postId = '')
        {
            //alias for get;
            return $this->get($postId);
        }
        public function canSave($postId, $post)
        {
            global $post;
            if (!isset($_POST[$this->_nonceName])
                || !wp_verify_nonce($_POST[$this->_nonceName], basename(__FILE__))
            ) {
                    return false;
            }
            if (!current_user_can('edit_post', $postId)) {
                return false;
            }
            //@codingStandardsIgnoreStart - camel-case
            if (!in_array($post->post_type, $this->for)) {
            //@codingStandardsIgnoreEnd
                return false;
            }
            return true;
        }
        public function setCustomSave($func)
        {
            if (!empty($func)
                && is_callable($func)
            ) {
                $this->customSave = $func;
                return true;
            } else {
                return false;
            }
        }
        public function hasCustomSave()
        {
            //return (!empty($this->customSave) && is_callable($this->customSave)) ? true : false;
            if (is_string($this->customSave)
                && !empty($this->customSave)
                && function_exists($this->customSave)
            ) {
                return true;
            } else {
                return false;
            }
        }
        private function _customSave($postId, $post, $update)
        {
            if ($this->hasCustomSave()) {
                //return $this->customSave($postId, $post, $update);
                return call_user_func($this->customSave, $this, $postId, $post, $update);
            } else {
                return $this->_defaultSave($postId, $post, $update);
            }
        }
        private function _defaultSave($postId, $post, $update = '')
        {
            global $post;
            $returnId = null;
            if ($this->canSave($postId, $post)) {
                if ($this->_inputType === 'checkbox') {
                    update_post_meta($postId, $this->name, ((isset($_POST[$this->name])) ? 'on' : 'off'));
                } else if (isset($_POST[$this->name])) {
                    $val = $this->sanitize($_POST[$this->name]);
                    if ($this->validate($val)) {
                        update_post_meta($postId, $this->name, $val);
                        $returnId = $postId;
                    }
                }
            }
            if ($returnId !== null) {
                return $returnId;
            } else {
                return;
            }
        }
        public function save($postId, $post, $update = '')
        {
            global $post;
            if ($this->hasCustomSave()) {
                return $this->_customSave($postId, $post, $update = '');
            } else {
                return $this->_defaultSave($postId, $post, $update = '');
            }
        }
        public function update($postId, $post, $update = '')
        {
            //is this alias for save? Writing as if is;
            return $this->save($postId, $post, $update);
        }
        public function getPHPSanitization()
        {
            switch ($this->_inputType) {
                case 'email':
                    return FILTER_SANITIZE_EMAIL;
                case 'url':
                    return FILTER_SANITIZE_URL;
                default:
                    return FILTER_SANITIZE_STRING;
            }
        }
        public function getPHPValidation()
        {
            switch ($this->_inputType) {
                case 'checkbox':
                case 'radio':
                    return FILTER_VALIDATE_BOOLEAN;
                case 'url':
                    return FILTER_VALIDATE_URL;
                case 'email':
                    return FILTER_VALIDATE_EMAIL;
                default:
                    return '';
            }
        }
        public function remove()
        {
        }
    }
}
