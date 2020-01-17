<?php
//@requirements
if (!class_exists('TTKey')) {
    require_once(sprintf("%s/inc/c/TTKey.php", dirname(__FILE__)));
}
if (!class_exists('ArgumentHandler')) {
    require_once(sprintf("%s/inc/c/ArgumentHandler.php", dirname(__FILE__)));
}
if (!function_exists('htmlToPCREPattern')) {
    require_once(sprintf("%s/inc/f/htmlToPCREPattern.php", dirname(__FILE__)));
}
if (!class_exists('UserFormStd')) {
    /**
     * @class UserFormStd
     * @constructor
     **/
    class UserFormStd 
    {
        public $before = '';
        public $after = '';
        public $order = 0;
        /**
         * @method __construct
         * @param args {Array}
         *      array(
         *          'before' => {String}
         *          'after' => {String}
         *          'order' => {Integer}
         *      )
         **/
        public function __construct($args = array())
        {
            if (isset($args) && is_array($args)) {
                if (isset($args['before'])) {
                    $this->setBefore($args['before']);
                }
                if (isset($args['after'])) {
                    $this->setAfter($args['after']);
                }
                if (isset($args['order'])) {
                    $this->setOrder($args['order']);
                }
            }
        }
        /**
         * @method setOrder
         * @class UserFormStd
         * @param $order [Integer]
         * @return $this->order
         **/
        public function setOrder($order)
        {
            $this->order = (is_numeric($order) && (int) $order == $order && $order > -1) ? $order : $this->order;
            return $this->order;
        }
        /**
         * @method viewBefore
         * @class UserFormStd
         * @param str {String}
         * @return str (if supplied) + $this->before
         **/
        public function viewBefore($str = '')
        {
            $str = (is_string($str)) ? $str : '';
            if (is_string($this->before) && strlen($this->before) > 0) {
                $str .= $this->before;
            }
            return $str;
        }
        /**
         * @method viewAfter
         * @class UserFormStd
         * @param str {String}
         * @return str (if supplied) + $this->after
         **/
        public function viewAfter($str = '')
        {
            $str = (is_string($str)) ? $str : '';
            if (is_string($this->after) && strlen($this->after) > 0) {
                $str .= $this->after;
            }
            return $str;
        }
        /**
         * @method setBefore
         * @class UserFormStd
         * @param $str {String}
         **/
        public function setBefore($str)
        {
            $this->before = (is_string($str)) ? $str : '';
        }
        /**
         * @method setAfter
         * @class UserFormStd
         * @param $str {String}
         **/
        public function setAfter($str)
        {
            $this->after = (is_string($str)) ? $str : '';
        }
    }
}
if (!function_exists('formOrderComparison')) {
    /**
     * @function formOrderComparison
     * @param $a {UserFormStd} object with order property
     * @param $b {UserFormStd} object with order property
     * @description used in usort for ordering
     **/
    function formOrderComparison($a, $b)
    {
        if (isset($a->order) && isset($b->order)) {
            return ($a->order < $b->order) ? -1 : 1;
        }
        return 0;
    }
}
if (!class_exists('UserForm')) {
    /**
     * @class UserForm
     * @constructor
     **/
    class UserForm extends UserFormStd
    {
        public $formObjects = [];
        public $formGroups = [];
        public $method = 'post';
        public $name = '';
        public $id = '';
        public $title= '';
        public $action = '';
        public $ajaxurl = '';
        public $submitLabel = 'Submit Form';
        public $submitTag = '';
        public $resetOveride = '';
        public $submissions = [];
        public $encoding = 'application/x-www-form-urlencoded';
        public $before = '';
        public $after = '';
        public $innerbefore = '';
        public $innerafter = '';

        private $_useJavascript = true;
        private $_useAjax = false;
        private $_namer = 'imform';
        private $_error = '';
        private $_send = '';
        private $_ajax = false;
        private $_reset = false;
        private $_received = false;
        private $_submitted = false;
        private $_overideSubmit = false;
        private $_overideReset = false;

        private $_use24hrLimit = false;

        public function __construct($args)
        {
            parent::__construct();
            //@codingStandardsIgnoreStart line-length
            $this->submissions = array(
                'success' => new UserFormSubmission('Thank you, your responses have been successfully received', false),
                'fail' => new UserFormSubmission('The information submitted has not been correctly processed, we have been informed of this error and will do our best to have it rectified shortly. Please try again in twenty-four hours', false),
                'problem' => new UserFormSubmission('There&#8217;s a mistake in the information provided, please review your responses.', true),
            );
            //@codingStandardsIgnoreEnd
            $acceptedArgs = array(
                'name' => 'name',
                'id' => 'id',
                'title' => 'title',
                'method' => 'post',
                'action' => 'actionUrl',
                'javascript' => true,
                'ajax' => 'ajaxUrl',
                'encoding' => 'encodingString',
                'send' => 'user string function',
                'submission' => '',
                'submitLabel' => 'Submit Form',
                'submitControl' => '',
                'before' => '',
                'after' => '',
                'innerbefore' => '',
                'innerafter' => '',
                'reset' => true
            );
            $ah = new ArgumentHandler($acceptedArgs);
            $p = $ah->passArguments($args);
            $this->setName($p['name']);
            if (isset($p['id'])) {
                $this->setId($p['id']);
            }
            if (isset($p['title'])) {
                $this->setTitle($p['title']);
            }
            if (isset($p['method'])) {
                $this->setMethod($p['method']);
            }
            if (isset($p['ajax'])) {
                $this->setAjax($p['ajax']);
            }
            if (isset($p['action'])) {
                $this->setAction($p['action']);
            }
            if (isset($p['encoding'])) {
                $this->setEncoding($p['encoding']);
            }
            if (isset($p['send'])) {
                $this->setSend($p['send']);
            }
            if (isset($p['javascript'])) {
                $this->setJavascript($p['javascript']);
            }
            if (isset($p['submission'])) {
                $this->setSubmission($p['submission']);
            }
            if (isset($p['submitLabel'])) {
                $this->setSubmitLabel($p['submitLabel']);
            }
            if (isset($p['submitControl'])) {
                $this->setSubmitControl($p['submitControl']);
            }
            if (isset($p['reset'])) {
                $this->setReset($p['reset']);
            }
            if (isset($p['before'])) {
                $this->setBefore($p['before']);
            }
            if (isset($p['after'])) {
                $this->setAfter($p['after']);
            }
            if (isset($p['innerbefore'])) {
                $this->setInnerBefore($p['innerbefore']);
            }
            if (isset($p['innerafter'])) {
                $this->setInnerAfter($p['innerafter']);
            }
            if (class_exists('UserFormPlugin')) {
                UserFormPlugin::addFormShortcode($this);
                UserFormPlugin::addWPAjax($this);
            }
        }
        public function setName($name)
        {
            if (is_string($name)) {
                //string, greater than 0, look at id naming conventions, 
                //? prefix with form name? if 'name' not allowed due to wordpress;
                $this->name = $name;
                $this->id = $name;
            } else {
                return false;
            }
        }
        public function setId($id)
        {
            if (is_string($id)) {
                $this->id = $id;
            } else {
                return false;
            }
        }
        public function setTitle($title)
        {
            if (is_string($title)) {
                $this->title = $title;
                return true;
            } else {
                return false;
            }
        }
        public function setEncoding($encoding)
        {
            $this->encoding = 'application/x-www-form-urlencoded';
            if (is_string($enconding)) {
                switch ($encoding) {
                    case 'text/plain':
                        $encoding = 'text/plain';
                        break;
                    case 'multipart/form-data':
                        $encoding = 'multipart/form-data';
                        break;
                    case 'application/x-www-form-urlencoded':
                    /*falls through*/
                    default:
                        $encoding = 'application/x-www-form-urlencoded';
                        break;
                }
            }
        }
        public function setSubmitLabel($str)
        {
            if (is_string($str)) {
                $this->submitLabel = $str;
                return true;
            } else {
                return false;
            }
        }
        public function setSubmitControl($str)
        {
            $this->submitTag = (is_string($str)) ? $str : $this->submitTag;
            $this->_overideSubmit = ($this->submitTag !== '') ? true : false;
        }
        public function setAjax($url)
        {
            if (is_string($url)) {
                $this->ajaxurl = $url;
                $this->setJavascript(true);
                $this->_useAjax = true;
                return true;
            }
            return false;
        }
        public function setJavascript($b = true)
        {
            if (is_string($b)) {
                $b = strtolower($b);
                $b = ($b === 'true') ? true : $b;
                $b = ($b === 'false') ? false : $b;
            }
            $this->_useJavascript = (is_bool($b)) ? $b : $this->_useJavascript;
        }
        public function setReset($b = true)
        {
            if (is_string($b)) {
                $b = strtolower($b);
                $b = ($b === 'true') ? true : $b;
                $b = ($b === 'false') ? false : $b;
            }
            $this->reset = (is_bool($b)) ? $b : $this->reset;
        }
        public function setInnerBefore($str)
        {
            if (is_string($str)) {
                $this->innerbefore = $str;
                return true;
            } else {
                return false;
            }
        }
        public function setInnerAfter($str)
        {
            if (is_string($str)) {
                $this->innerafter = $str;
                return true;
            } else {
                return false;
            }
        }
        public function setSend($str = '')
        {
            if (is_string($str) && strlen($str) > 0 && function_exists($str)) {
                $this->_send = $str;
                return true;
            }
            return false;
        }
        public function setReceived($boo = true)
        {
            $this->_received = ($boo === true) ? true : $this->_received;
        }
        public function inReceipt()
        {
            return $this->_received;
        }
        public function set24HourLimit($boo = true)
        {
            $this->_use24hrLimit = (is_bool($boo)) ? $boo : $this->_use24hrLimit;
        }
        public function resetAll()
        {
            $arr = $this->getAllFormObjects();
            foreach ($arr as $a) {
                $a->defaultValue();
            }
            return true;
        }
        public function setMethod($method)
        {
            $method = (is_string($method)) ? strtolower($method) : 'post';
            $this->method = ($method === 'get') ? 'get' : 'post';
        }
        public function send()
        {
            if ($this->hasUserData()) {
                if (!$this->hasError()) {
                    if (!$this->_use24hrLimit || $this->limitTo24hours()) {
                        if (is_string($this->_send) && strlen($this->_send) > 0 && function_exists($this->_send)) {
                            $a = call_user_func($this->_send, $this->sendObject());
                            $this->_submitted = (!(isset($a))) ? 'success' : $a;
                        }
                        return ($this->_submitted === 'success') ? true : false;
                    } else {
                        $this->_submitted = '24hour';
                        return false;
                    }
                } else {
                    $this->_submitted = 'problem';
                    return false;
                }
            }
            return false;
        }
        public function limitTo24Hours()
        {
            $sessionVar = $this->name.'_cml';
            if (session_id() !== '') {
                $okay = true;
                $t = crypt(date('Y-M-D', time()));
                if ($_SESSION[$sessionVar] === $t) {
                    $okay = false;
                }
                $_SESSION[$sessionVar] = $t;
                return $okay;
            } else {
                return true;
            }
        }
        public function setup()
        {
            if ($this->hasUserData()) {
                $this->receiveUserData();
                if ($this->isValid()) {
                    //need limiters here;
                    $r = $this->send();
                    if ($r === true) {
                        //$this->_submitted = true;
                        $this->resetAll();
                    }
                }
            }
        }
        public function hasUserData()
        {
            $a = '';
            if (defined('INPUT_POST') || defined('INPUT_GET')) {
               $method = ($this->method === 'post') ? INPUT_POST : INPUT_GET;
               $a = filter_input($method, $this->_namer);
               $b = filter_input($method, 'turing');
               $c = filter_input($method, 'nonce');
            } else {
                $arr = ($this->method === 'post') ? $_POST : $_GET;
                $a = (isset($arr[$this->_namer]) && is_string($arr[$this->_namer])) ? $arr[$this->_namer] : '';
                $b = (isset($arr['turing']) && is_string($arr['turing'])) ? $arr['turing'] : '';
                $c = (isset($arr['nonce']) && is_string($arr['nonce'])) ? $arr['nonce'] : '';
            }
            return ($a === $this->name && validateTTKey($b) && $this->validateWPNonce($c));
        }
        public function receiveUserData()
        {
            if ($this->hasUserData()) {
                $this->_received = true;
                foreach ($this->formObjects as $k => $v) {
                    $v->receive();
                }
                foreach ($this->formGroups as $v) {
                    foreach ($v->formObjects as $w) {
                        $w->receive();
                    }
                }
            }
        }
        public function receive($formObj)
        {
            $method = ($this->method == 'post' || $this->method == 'get') ? $this->method : 'post';
            if (defined('INPUT_POST') || defined('INPUT_GET')) {
                // @codingStandardsIgnoreStart
                $a = filter_input((($method === 'get') ? INPUT_GET : INPUT_POST), $formObj->name, $formObj->getPHPSanitize());
                return $a;
                // @codingStandardsIgnoreEnd
            } else {
                return ($method === 'post') ? $_POST[$formObj->name] : $_GET[$formObj->name];
            }
            return '';
        }
        public function ajax()
        {
            $this->setup();
            echo $this->view();
            if (function_exists('wp_die')) {
                wp_die();
            } else {
                die();
            }
        }
        public function html()
        {
            $this->setup();
            return $this->view();
        }

        public function hasError()
        {
            if ($this->_error !== '') {
                $error = false;
                foreach ($this->formObjects as $k => $v) {
                    if ($k->hasError()) {
                        $error = true;
                        break;
                    }
                }
                $this->_error = $error;
                return $error;
            } else {
                return $this->_error;
            }
        }
        public function isValid()
        {
            $error = false;
            foreach ($this->formObjects as $k => $v) {
                if ($v->isRequired() && $v->hasError()) {
                    $error = true;
                    break;
                }
            }
            return ($error) ? false : true;
        }
        public function displayErrors()
        {
            return ($this->_received && $this->hasError());
        }
        public function sendObject()
        {
            $objs = $this->getAllFormObjects();
            $arr = array();
            foreach ($objs as $o) {
                if ($o->validate() === true) {
                    $arr[$o->name] = $o->value;
                }
            }
            return $arr;
        }
        public function setSubmission($name, $val)
        {
            if (isset($this->submissions[$name])) {
                if (is_string($val)) {
                    $this->submissions[$name]->message = $val;
                    return true;
                } else if (is_bool($val)) {
                    $this->submissions[$name]->show = $val;
                    return true;
                } else if (is_a($val, 'UserFormSubmission')) {
                    $this->submissions[$name] = $val;
                    return true;
                }
            }
            return false;
        }
        public function setSubmissions($arr = array())
        {
            if (is_array($arr)) {
                $hasSet = false;
                if (isset($arr['success'])) {
                    $hasSet = ($this->setSubmission('success', $arr['success'])) ? true : $hasSet;
                }
                if (isset($arr['fail'])) {
                    $hasSet = ($this->setSubmission('fail', $arr['fail'])) ? true : $hasSet;
                }
                if (isset($arr['24hr'])) {
                    $hasSet = ($this->setSubmission('fail', $arr['fail'])) ? true : $hasSet;
                }
                return ($hasSet);
            } else if (is_string($arr)) {
                return ($this->setSubmission('success', $arr));
            }
            return false;
        }
        public function getAllFormObjects()
        {
            $arr = $this->formObjects;
            for ($i=0, $imax=count($this->formGroups); $i<$imax; $i+=1) {
                foreach ($this->formGroups[$i]->formObjects as $v) {
                    $arr[] = $v;
                }
            }
            return $arr;
        }
        public function viewSubmissionMessage()
        {
            $str = '';
            if (is_string($this->_submitted)) {
                if (isset($this->submissions[$this->_submitted])) {
                    $str .= $this->submissions[$this->_submitted]->message;
                }
            }
            if (!empty($str)) {
                $str = '<div class="submission"><p>'.$str.'</p></div>';
            }
            return $str;
        }
        public function viewInnerBefore()
        {
            if (is_string($this->innerbefore) && !empty($this->innerbefore)) {
                return $this->innerbefore;
            }
            return '';
        }
        public function viewInnerAfter()
        {
            if (is_string($this->innerafter) && !empty($this->innerafter)) {
                return $this->innerafter;
            }
            return '';
        }
        public function view()
        {
            $str = '<div id="'.$this->name.'_container">';
                $str .= $this->viewBefore();
                $str .= (!$this->shouldShowForm()) ? '<span id="'.$this->name.'" name="'.$this->name.'"></span>' : '';
                $str .= $this->viewSubmissionMessage();
                if ($this->shouldShowForm()) {
                    $str .= $this->viewForm();
                }
                $str .= $this->viewAfter();
            return $str.'</div>';
        }
        public function shouldShowForm()
        {
            $showForm = true;
            if ($this->_submitted !== false) {
                if (is_bool($this->_submitted)) {
                    $showForm = ($this->_submitted) ? false : true;
                } else if (is_string($this->_submitted)) {
                    if (isset($this->submissions[$this->_submitted])) {
                        $showForm = $this->submissions[$this->_submitted]->show;
                    } else {
                        //true, success = false: fail, false, problem anything else = false;
                        switch ($this->_submitted) {
                            case 'true':
                            case 'success':
                            case 'fail':
                            case 'problem':
                               $showForm = false;
                            default:
                                $showForm = true;
                        }
                    }
                }
            }
            return $showForm;
        }
        public function viewForm()
        {
            $str = '';
            //@codingStandardsIgnoreStart - line-length;
            $str .= '<form id="'.$this->id.'" name="'.$this->name.'" method="'.$this->method.'" action="'.$this->action.'#'.$this->id.'" enctype="'.$this->encoding.'"';
                //@codingStandardsIgnoreEnd
                    $str .= ($this->_useAjax) ? ' data-ajaxurl="'.$this->ajaxurl.'" ' : '';
                    $str .= ($this->_useJavascript) ? ' class="imform"' : '';
                $str .= '>';
                $str .= $this->viewInnerBefore();
                $str .= (!empty($this->title)) ? '<legend>'.$this->title.'</legend>' : '';
                $str .= '<input type="hidden" name="'.$this->_namer.'" value="'.$this->name.'" />';
                $str .= $this->viewTT();
                $str .= $this->viewWPAction();
                $str .= $this->viewWPNonce();
                $arr = array_merge($this->formObjects, $this->formGroups);
                usort($arr, 'formOrderComparison');
                for ($i = 0, $imax = count($arr); $i < $imax; $i+=1) {
                    if (method_exists($arr[$i], 'view')) {
                        $str .= $arr[$i]->view();
                    }
                }
                $str .= $this->viewControlButtons();
                $str .= $this->viewInnerAfter();
            $str .= '</form>';
            return $str;
        }
        public function viewTT()
        {
            return '<input type="hidden" name="turing" value="'.generateTTKey().'" />';
        }
        public function validateWPNonce($nonce)
        {
            if (function_exists('wp_verify_nonce') && function_exists('wp_create_nonce')) {
                return (wp_verify_nonce($nonce, $this->name.'_nonce')) ? true : false;
            }
            return true;
        }
        public function viewWPAction()
        {
            if (function_exists('add_action')) {
                return '<input type="hidden" name="action" value="uf_'.$this->name.'" />';
            }
            return '';
        }
        public function viewWPNonce()
        {
            if (function_exists('wp_verify_nonce') && function_exists('wp_create_nonce')) {
                $nonce = wp_create_nonce($this->name.'_nonce');
                return '<input type="hidden" name="nonce" value="'.$nonce.'" />';
            }
            return '';
        }
        public function viewControlButtons()
        {
            $str = "";
            //@codingStandardsIgnoreStart line-length
            $str .= ($this->_overideSubmit) ? $this->submitTag : '<input type="submit" value="'.$this->submitLabel.'" />';
            $str .= ($this->_reset) ? (($this->_overideReset) ? $this->resetOveride : '<input type="reset" value="Reset Form" />') : '';
            //@codingStandardsIgnoreEnd
            return $str;
        }
        public function addFormObject($obj)
        {
            if (is_a($obj, 'UserFormObject')) {
                $count = count($this->formObjects);
                $obj->setMaster($this);
                $this->formObjects[] = $obj;
                //$this->formObjects = array_unique($this->formObjects);
                return (count($this->formObjects) > $count);
            }
            return false;
        }
        public function removeFormObject($obj)
        {
            $done = false;
            if (is_a($obj, 'UserFormObject')) {
                for ($i=0; $i < count($this->formObjects); $i += 1) {
                    if ($this->formObjects[$i] == $obj) {
                        array_splice($this->formObjects, $i, 1);
                        $done = true;
                        $i -= 1;
                    }
                }
            }
            return $done;
        }
        public function addFormGroup($grp)
        {
            if (is_a($grp, 'UserFormGroup')) {
                $count = count($this->formGroups);
                foreach ($grp->formObjects as $v) {
                    $v->setMaster($this);
                }
                $this->formGroups[] = $grp;
                //$this->formGroups = array_unique($this->formGroups);
                return (count($this->formGroups) > $count);
            }
            return false;
        }
        public function removeFormGroup($grp)
        {
            $done = false;
            if (is_a($grp, 'UserFormGroup')) {
                for ($i=0; $i < count($this->formGroups); $i += 1) {
                    if ($this->formGroups[$i] == $obj) {
                        array_splice($this->formGroups, $i, 1);
                        $done = true;
                        $i -= 1;
                    }
                }
            }
            return $done;
        }
    }
}
if (!class_exists('UserFormGroup')) {
    class UserFormGroup extends UserFormStd
    {
        public $order = 0;
        public $formObjects = [];

        public function __construct($args)
        {
            parent::__construct($args);
            $acceptedArgs = array('order'=>0);
            $ah = new ArgumentHandler($acceptedArgs);
            $p = $ah->passArguments($args);
        }

        public function addFormObject($obj)
        {
            if (is_a($obj, 'UserFormObject')) {
                $this->formObjects[] = $obj;
            }
        }
        public function removeFormObject($obj)
        {
            $done = false;
            if (is_a($obj, 'UserFormObject')) {
                for ($i=0; $i < count($this->formObjects); $i += 1) {
                    if ($this->formObjects[$i] == $obj) {
                        array_splice($this->formObjects, $i, 1);
                        $done = true;
                        $i -= 1;
                    }
                }
            }
            return $done;
        }
        public function view()
        {
            $str = '';
            $str .= $this->viewBefore();
            $arr = $this->formObjects;
            usort($arr, 'formOrderComparison');
            for ($i = 0, $imax = count($arr); $i < $imax; $i += 1) {
                if (method_exists($arr[$i], 'view')) {
                    $str .= $arr[$i]->view();
                }
            }
            $str .= $this->viewAfter();
            return $str;
        }
    }
}
if (!class_exists('UserFormObject')) {
    class UserFormObject extends UserFormStd
    {
        public $type;
        public $name = [];
        public $pattern;
        public $alerts = array('default' => 'The information supplied doesn&#8217;t seem correct');
        public $attrs = array('placeholder' => '');
        public $order;
        public $default;
        public $value;
        public $autocomplete = true;
        private $_required = true;
        private $_master;
        private $_received = false;
        private $_valid = false;
        private $_custom = '';
        private $_form;
        public function __construct($args)
        {
            $acceptedArgs = array(
                'type' => 'text',
                'name' => '',
                'id' => '',
                'default' => '',
                'master' => '',
                'autocomplete' => true,
                'placeholder' => '',
                'pattern' => '',
                'required' => true,
                'order' => 0,
                'alerts' => array(),
                'before' => '',
                'after' => '',
                'customValidation' => '',
                'label' => '');
            $ah = new ArgumentHandler($acceptedArgs);
            $p = $ah->passArguments($args);
            $p['name'] = ($p['name'] === '' && $p['id'] !== '') ? $p['id'] : $p['name'];
            if ($p['name'] !== '') {
                if (isset($p['name'])) {
                    $this->setName($p['name']);
                }
                if (isset($p['type'])) {
                    $this->setType($p['type']);
                }
                if (isset($p['default'])) {
                    $this->setDefault($p['default']);
                }
                if (isset($p['pattern'])) {
                    $this->setPattern($p['pattern']);
                }
                if (isset($p['master'])) {
                    $this->setMaster($p['master']);
                }
                if (isset($p['autocomplete'])) {
                    $this->setAutoComplete($p['autocomplete']);
                }
                if (isset($p['required'])) {
                    $this->setRequired($p['required']);
                }
                if (isset($p['order'])) {
                    $this->setOrder($p['order']);
                }
                if (isset($p['before'])) {
                    $this->setBefore($p['before']);
                }
                if (isset($p['after'])) {
                    $this->setAfter($p['after']);
                }
                if (isset($p['placeholder'])) {
                    $this->setPlaceholder($p['placeholder']);
                }
                if (isset($p['label'])) {
                    $this->setLabel($p['label']);
                }
                if (isset($p['alerts'])) {
                    $this->setAlerts($p['alerts']);
                }
                if (isset($p['customValidation'])) {
                    $this->setCustom($p['customValidation']);
                }
            }
        }
        public function setMaster($master)
        {
            if (is_a($master, 'UserForm')) {
                $this->_master = $master;
                return true;
            }
            return false;
        }
        public function setDefault($val)
        {
            $this->default = $val;
        }
        public function defaultValue()
        {
            $this->value = $this->default;
        }
        public function setLabel($labelText)
        {
            $this->label = (is_string($labelText)) ? $labelText : $this->label;
        }
        public function setOrder($order)
        {
            $this->order = (is_numeric($order) && (int) $order == $order && $order > -1) ? $order : $this->order;
            return $this->order;
        }
        public function setRequired($boo = true)
        {
            $this->_required = ($boo === false) ? false : true;
            return $this->_required;
        }
        public function setAutoComplete($boo = true)
        {
            $boo = (is_bool($boo)) ? (boolean) $boo : true;
            $this->attrs['autocomplete'] = $boo;
        }
        public function setCustom($functionName)
        {
            if ($functionName !== '' && function_exists($functionName)) {
                $this->_custom = $functionName;
            }
        }
        public function setType($type)
        {
            $type = (is_string($type)) ? $type : '';
            $type = strtolower($type);
            switch ($type) {
                case 'textarea':
                case 'tel':
                case 'url':
                case 'radio':
                case 'checkbox':
                case 'hidden':
                case 'email':
                    break;
                default:
                    $type = 'text';
            }
            $this->type = $type; 
            return $this->type;
        }
        public function setPattern($pattern)
        {
            $this->pattern = (is_string($pattern)) ? $pattern : '';
            //better check here first character should be a /, etc.
            return $this->pattern;
        }
        public function setName($name)
        {
            if (is_string($name)) {
                //string, greater than 0, look at id naming conventions, 
                //? prefix with form name? if 'name' not allowed due to wordpress;
                $this->name = $name;
                $this->id = $name;
            } else {
                return false;
            }
        }
        public function setAlerts($args)
        {
            if (is_array($args)) {
                if (isset($args['default'])) {
                    $this->alerts['default'] = $args['default'];
                }
                if (isset($args['pattern'])) {
                    $this->alerts['pattern'] = $args['pattern'];
                }
                if (isset($args['custom'])) {
                    $this->alerts['custom'] = $args['custom'];
                }
            } else if (is_string($args)) {
                $this->alerts['default'] = $args;
            }
        }
        public function setPlaceholder($place)
        {
            if (is_string($place) && strlen($place) > 0) {
                $this->attrs['placeholder'] = strip_tags($place);
                return true;
            } else {
                return false;
            }
        }
        public function isRequired()
        {
            return ($this->_required);
        }
        public function hasCustom()
        {
            if ($this->_custom !== '') {
                if (function_exists($this->_custom)) {
                    return true;
                } else {
                    $this->_custom = '';
                    return false;
                }
            } else {
                return false;
            }
        }
        public function receive()
        {
            if (is_a($this->_master, 'UserForm')) {
                $val = $this->_master->receive($this);
                $val = $this->sanitize($val);
                if ($val !== '') {
                    $this->_received = true;
                    $this->_master->setReceived(true);
                    $this->value = $val;
                    $this->validate($val);
                }
            }
        }
        public function getPHPSanitize()
        {
            switch ($this->type) {
                case 'email':
                    return (defined('FILTER_SANITIZE_EMAIL')) ? FILTER_SANITIZE_EMAIL : '';
                case 'url':
                    return (defined('FILTER_SANITIZE_URL')) ? FILTER_SANITIZE_URL : '';
                default:
                    return (defined('FILTER_DEFAULT')) ? FILTER_DEFAULT : '';
            }
        }
        public function sanitize($val)
        {
            switch ($this->type) {
                case 'radio':
                case 'checkbox':
                    $val = (filter_var($val, FILTER_VALIDATE_BOOLEAN)) ? $val : '';
                    break;
                case 'url':
                    $val = filter_var($val, FILTER_SANITIZE_URL);
                case 'email':
                    $val = filter_var($val, FILTER_SANITIZE_EMAIL);
                    break;
                case 'number':
                    //@codingStandardsIgnoreStart - line-length;
                    $val = (filter_var($val, FILTER_VALIDATE_INT) || filter_var($val, FILTER_VALIDATE_FLOAT)) ? $val : '';
                    //@codingStandardsIgnoreEnd
                    break;
                default:
                    $val = filter_var($val, FILTER_SANITIZE_STRING);
                    if (function_exists('sanitize_text_field')) { //wordpress sanitize information
                        $val = sanitize_text_field($val);
                    }
                    $val = strip_tags($val); //not allowing html may need to be moved;
                    $val = stripslashes($val); //remove slashing;
                    break;
            }
            return $val;
        }
        public function validate()
        {
            $error = 0;
            if (!$this->validateDefault()) {
                return 'default';
            }
            if (!$this->validatePattern()) {
                return 'pattern';
            }
            if (!$this->validateCustom()) {
                return 'custom';
            }
            return true;
        }
        public function validateDefault()
        {
            switch ($this->type) {
                case 'number':
                    //@codingStandardsIgnoreStart line-length
                    return (filter_var($this->value, FILTER_VALIDATE_INT) || filter_var($this->value, FILTER_VALIDATE_FLOAT));
                    //@codingStandardsIgnoreEnd
                case 'checkbox':
                case 'radio':
                    $b = $this->value;
                    if (is_string($b)) {
                        $b = strtolower($b);
                    }
                    if ($b == 'true' || $b == '1' || $b === true || $b == 1) {
                        $b = true;
                    } else if ($b == 'false' || $b == '0' || $b === false || $b == 0) {
                        $b = false;
                    }
                    return is_bool($b);
                case 'email':
                    return filter_var($this->value, FILTER_VALIDATE_EMAIL);
                case 'url':
                    return filter_var($this->value, FILTER_VALIDATE_URL);
                case 'text':
                case 'hidden':
                case 'password':
                case 'tel':
                case 'textarea':
                    return is_string($this->value);
            }
        }
        public function validatePattern()
        {
            if (strlen($this->pattern) === 0) {
                return true; //no pattern, so assumed valid
            } else {
                $hasError = false;
                try {
                    $pattern = htmlToPCREPattern($this->pattern);
                    $pattern = '/'.$pattern.'/u'; //may need to do unicode flag here!
                    $hasError = (preg_match($pattern, $this->value)) ? false : true;
                } catch (Exception $e) {
                    $hasError = false; //if pattern is invalid, return false;
                }
                return ($hasError) ? false : true;
            }
        }
        public function validateCustom()
        {
            if ($this->hasCustom()) {
                return (call_user_func($this->_custom, $this->value, $this) === false) ? false : true;
            }
            return true;
        }
        public function hasError()
        {
            return ($this->validate() === true) ? false : true;
        }
        public function viewAlerts()
        {
            //@codingStandardsIgnoreStart line-length
            $showErrors = ((($this->_master->inReceipt() && $this->_required) || $this->_received) && $this->hasError());
            //@codingStandardsIgnoreEnd
            $onError = '';
            if ($showErrors) {
                $onError = $this->validate();
                if (!isset($this->alerts[$onError])) {
                    $onError = 'default';
                }
            }
            $str = '';
            $arr = array('default', 'pattern', 'custom');
            for ($i = 0, $imax = count($arr); $i < $imax; $i += 1) {
                if (isset($this->alerts[$arr[$i]])) {
                    $str .= '<p id="'.$this->name.'--'.$arr[$i].'" role="alert" aria-owns="'.$this->name.'" ';
                    if ($showErrors && $onError == $arr[$i]) {
                        $str .= 'class="alert on" aria-hidden="false"';
                    } else {
                        $str .= 'class="alert" aria-hidden="true"';
                    }
                    $str.= '>'.$this->alerts[$arr[$i]].'</p>';
                }
            }
            return $str;
        }
        public function viewLabel()
        {
            if (is_string($this->label) && strlen($this->label) > 0) {
                return '<label for="'.$this->id.'">'.$this->label.'</label> ';
            }
            return '';
        }
        public function view()
        {
            $str = '';
            $str .= '<fieldset>';
                $str .= $this->viewBefore();
                $str .= $this->viewAlerts();
                $str .= $this->viewLabel();
            //fuck up areas - hidden shouldn't have before or after, file?
            switch ($this->type) {
                case 'checkbox':
                case 'radio':
                    //@codingStandardsIgnoreStart - line-length
                    $str .= '<input type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" value="'.$this.viewValue().'" '.$this->viewAttributes(array('placeholder')).' />';
                    //@codingStandardsIgnoreEnd
                    break;
                case 'text':
                case 'tel':
                case 'url':
                case 'email':
                    //@codingStandardsIgnoreStart - line-length
                    $str .= '<input type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->viewValue().'" '.$this->viewAttributes().$this->viewPattern().' />';
                    //@codingStandardsIgnoreEnd
                    break;
                case 'textarea':
                    //@codingStandardsIgnoreStart - line-length
                    $str .= '<textarea name="'.$this->name.'" id="'.$this->name.'" '.$this->viewPattern().$this->viewAttributes().'>'.$this->viewValue().'</textarea>';
                    //@codingStandardsIgnoreEnd
                    break;
            }
            $str .= $this->viewAfter();
            $str .= '</fieldset>';
            return $str;
        }
        public function viewAttributes($excludeArray = '')
        {
            $excludeArray = (!is_array($excludeArray)) ? array($excludeArray) : $excludeArray;
            $str = '';
            $str .= ($this->_required) ? 'required="true" ' : '';
            if (isset($this->attrs['autocomplete']) && !in_array('autocomplete', $excludeArray)) {
                $str .= 'autocomplete="'.(($this->attrs['autocomplete']) ? 'true' : 'false').'" ';
            }
            if (isset($this->attrs['placeholder'])) {
                $str .= 'placeholder="'.$this->attrs['placeholder'].'" ';
            }
            // remove the exclude args from current array;
            return $str;
        }
        public function viewValue()
        {
            $str = '';
            if ($this->_received) {
                $str = htmlentities($this->value);
            }
            return $str;
        }
        public function viewPattern()
        {
            $str = '';
            //should catch type = radio, checkbox, date etc. here;
            if (is_string($this->pattern) && strlen($this->pattern) > 0) {
                try {
                    //test is a valid pattern
                    $str = ' pattern="'.$this->pattern.'"';
                } catch(Exception $e) {
                }
            }
            return $str;
        }
        public function viewPlaceholder()
        {
            $str = '';
            //should catch type = radio, checkbox, date etc. here
            if (is_string($this->placeholderText) && strlen($this->placeholderText) > 0) {
                $txt = strreplace('"', '', strip_tags($this->placeholderText));
                if (strlen($txt) > 0) {
                    $str = ' placeholder="'.$txt.'"';
                }
            }
            return $str;
        }
    }
}
if (!class_exists('UserFormSubmission')) {
    class UserFormSubmission
    {
        public $showForm = false;
        public $message = '';
        public function __construct($message, $show = false)
        {
            $this->message = (is_string($message)) ? $message : '';
            $this->show = (is_bool($show)) ? $show : false;
        }
    }
}
