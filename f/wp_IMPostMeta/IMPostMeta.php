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

        private $_nonceName = '';
        private $_storedValue = '';

        private $_inputType = 'text';
        private $_inputPattern = '';

        private $_defaultDisplay = true;
        public $customAdminInit = '';

        private $_layoutArea = 'side';
        private $_layoutPriority = 'high';

        private $_defaultView = true;
        public $customView = '';

        public function __construct($obj)
        {
            $this->_nonceName = 'meta-box-'.$this->name;
        }
        public function init()
        {
            
        }
        public function sanitize($v)
        {
            return filter_var($v, $this->getPHPSanitization());
        }
        public function validate($v)
        {
            if (filter_var($v, $this->getPHPValidation())) {
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
        public function setCustomAdminView($func = '')
        {
            if (is_string($func)
                && !empty($func)
                && function_exists($func)
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
        public function setCustomAdminInit($func)
        {
            if (is_string($func)
                && !empty($func)
                && function_exists($func)
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
        public function create()
        {
            $metaOk = false;
            if (
                function_exists('post_type_exists')
                && post_type_exists($this->for)
            ) {
                if (
                    function_exists('post_type_supports') 
                    && post_type_supports($this->for, 'custom-fields')
                ) {
                    $metaOk = true;
                } else {
                    if (function_exists('add_post_type_support')) {
                        add_post_type_support('custom-fields');
                        $metaOk = true;
                    } else {
                        throw new Exception(
                            "IMPostMeta cannot initialise
                            custom-fields on the chosen post-type"
                        );
                    }
                }
            } else {
                throw new Exception(
                    "IMPostMeta cannot function for ".$this->name." as the
                    post-type '".$this->for."' does not exist"
                );
            }
            if ($metaOk) {
                add_action('add_meta_boxes_'.$this->for, array($this, 'adminInit'));
                add_action('pre_post_update', array($this, 'save'), 10, 3);
                add_action('save_post_'.$this->for, array($this, 'save'), 10, 3);
            }
        }
        public function adminInit()
        {
            if ($this->_defaultDisplay) {
                if (function_exists('add_meta_box')) {
                    add_meta_box(
                        $this->name,
                        __($this->name),
                        array($this, 'edit'),
                        array($this->_layoutArea),
                        array($this->_layoutPriority)
                    );
                }
            } else {
                if (!empty($this->customAdminInit)
                    && function_exists($this->customAdminInit)
                ) {
                    call_user_func($this->customAdminInit, $this);
                }
            }
        }
        public function adminDefaultDisplay($boo)
        {
            $this->_defaultDisplay = (is_bool($boo)) ? $boo : $this->_defaultDisplay;
            return ($this->_defaultDisplay === $boo);
        }
        public function edit()
        {
            if ($this->_defaultView) {
                $this->_adminView();
            } else {
                $this->_customView();
            }
        }
        private function _customView()
        {
            if (!empty($this->customView)
                && is_string($this->customView)
                && function_exists($this->customView)
            ) {
                call_user_func($this->customView, $this);
            } else {
                $this->_adminView();
            }
        }
        private function _adminView()
        {
            $str = '<fieldset>';
            wp_nonce_field(basename(__FILE__), $this->_nonceName);
            $str = $str.'<label for="'.$this->name.'">'.$this->name.'</label>';
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
                    $str = $str.' value="'.$this->get().'" />';
            }
            $endstr = $endstr.'<p>'.$this->description.'</p>';
            return $str.$endstr.'</fieldset>';
        }
        public function get($postId = '')
        {
            if (!empty($this->_storedValue)) {
                return $this->_storedValue;
            } else {
                $post = getPostId($postId);
                if ($postId !== false) {
                    $val = get_post_meta($postId, $this->name, true);
                    $val = $this->sanitize($val);
                    $val = ($this->validate($val)) ? $val : '';
                    $this->_storedValue = $val;
                    return $val;
                }
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
            if (!isset($_POST[$this->nonceName])
                || !wp_verify_nonce($_POST[$this->nonceName])
            ) {
                    return false;
            }
            if (!current_user_can('edit_post', $postId)) {
                return false;
            }
            //@codingStandardsIgnoreStart - camel-case
            if ($this->for != $post->post_type) {
            //@codingStandardsIgnoreEnd
                return false;
            }
            return true;
        }
        public function save($postId, $post, $update = '')
        {
            global $post;
            $returnId = null;
            if ($this->canSave($postId, $post)) {
                if (isset($_POST[$this->name])) {
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
        public function update($postId, $post, $update = '')
        {
            //is this alias for save? Writing as if is;
            return $this->save($postId, $post, $update);
        }
        public function getPHPSanitization()
        {
            switch ($this->_inputType) {
                case 'email':
                    return 'email';
                case 'url':
                    return 'url';
                default:
                    return 'string';
            }
        }
        public function getPHPValidation()
        {
            switch ($this->_inputType) {
                case 'url':
                    return 'validate_url';
                case 'email':
                    return 'validate_email';
                default:
                    return '';
            }

        }
        public function remove()
        {
        }
    }
}
