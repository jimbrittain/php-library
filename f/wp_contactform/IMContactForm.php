<?php
defined('ABSPATH') or die('');

if (!class_exists('IMContactForm')) {
    /**
     * @class IMContactForm
     */
    class IMContactForm
    {
        private $_method = 'post';
        private $_to = '';
        private $_salt = '';
        private $_content = '';
        private $_active = false;
        public $view = '';
        public static $written = false;
        public static $formname = 'imformname';
        public static $formvalue = 'imcontactform';
        public function __construct($name = '')
        {
            $this->requires();
            if (empty($this->_to)) {
                $this->_to = IMContactFormPlugin::getTo();
            }
            self::$formname = (!empty($name) && is_string($name)) ? $name : 'imformname';
            $this->_active = (!empty($this->_to)) ? true : false;
            $this->_content = new IMContactFormContent();
            $this->view = new IMContactFormView($this);
        }
        /**
         * Loader for dependent classes assumes that they are in the same folder
         * as this plugin file.
         * @method requires
         * @requires isEmailAddress, isFullName, TTKey, IMContactFormContent,
         *  IMContactFormView
         */
        public function requires()
        {
            $dir = sprintf("%s/", dirname(__FILE__));
            if (!function_exists('isEmailAddress')) {
                include_once($dir.'isEmailAddress.php');
            }
            if (!function_exists('isFullName')) {
                include_once($dir.'isFullName.php');
            }
            if (!class_exists('TTKey')) {
                include_once($dir.'TTKey.php');
            }
            if (!class_exists('IMContactFormContent')) {
                include_once($dir.'IMContactFormContent.php');
            }
            if (!class_exists('IMContactFormView')) {
                include_once($dir.'IMContactFormView.php');
            }
        }
        /**
         * Provides HTML Output, sends data if data found and valid
         * @method html
         * @return {String} 
         */
        public function html()
        {
            $str = "";
            if ($this->hasUserData()) {
                $this->discoverUserData();
                if ($this->_content->vaild()) {
                    $this->attemptToSend();
                } else {
                    $str = $this->view->form(true);
                }
            } else {
                $this->_content = new IMContactFormContent();
                $str = $this->view->form();
            }
            return $str;
        }
        /**
         * Confirms by checking GET|POST data if user data has been passed
         * @method hasUserData
         * @return {Boolean}
         */
        public function hasUserData()
        {
            $a = '';
            if (defined('INPUT_POST') || defined('INPUT_GET')) {
               $this->_method = ($this->_method === 'post') ? INPUT_POST : INPUT_GET;
               $a = filter_input($this->_method, self::$formname);
            } else {
                if ($this->_method === 'post') {
                    // @codingStandardsIgnoreStart
                    $a = (isset($_POST[self::$formname]) && is_string($_POST[self::$formname])) ? $_POST[self::$formname] : '';
                    // @codingStandardsIgnoreEnd
                } else {
                    // @codingStandardsIgnoreStart
                    $a = (isset($_POST[self::$formname]) && is_string($_POST[self::$formname])) ? $_POST[self::$formname] : '';
                    // @codingStandardsIgnoreEnd
                }
            }
            return ($a === self::$formvalue);
        }
        /**
         * Retrieval for user data, and adding to content object
         * @method discoverUserData
         */
        public function discoverUserData()
        {
            if ($this->hasUserData()) {
                $this->_content->findAll($this->_method);
            }
        }
        /**
         * Uses session to limit the sending to one per calendar day
         * @method limitTo24Hours
         * @return {Boolean}
         */
        public function limitTo24Hours()
        {
            if (session_id() !== '') {
                $okay = true;
                $t = crypt(date('Y-M-D'), time());
                if ($_SESSION['cml'] === $t) {
                    $okay = false;
                }
                $_SESSION['cml'] = $t;
                return $okay;
            } else {
                return true;
            }
        }
        /**
         * @method ajax
         */
        public function ajax()
        {
            $str = "";
            if ($this->hasUserData()) {
                $userData = $this->discoverUserData();
                if ($this->_content->valid()) {
                    $sent = $this->attemptToSend();
                    if ($sent) {
                        $this->_content = new IM_ContactFormContent();
                        $str .= $this->view->form();
                        $str .= $this->view->overlay('submission');
                    } else {
                        $str .= $this->view->form(true);
                        $str .= $this->view->overlay('inactive');
                    }
                } else {
                    $str .= $this->view->form(true);
                    $str .= $this->view->overlay('error');
                }
            } else {
                $str = $this->view->form();
            }
            echo $str;
        }
        /**
         * @method attemptToSend
         * @return {Boolean} true = sent
         */
        public function attemptToSend()
        {
            if ($this->_content->valid() && $this->limitTo24hours()) {
                $to = $this->_to;
                $subject = "Website Inquiry";
                $message = $this->_content->message;
                $headers = 'From: '.$this->_content->name.'<'.$this->_content->email.">\r\n";
                $headers .= 'Reply-To: '.$this->_content->email."\r\n";
                $m = imap_mail($to, $subject, $message, $headers);
                if ($m === false) {
                    mail($o, $subject, $message, $headers);
                }
                return $m;
            } else {
                return false;
            }
        }
        /**
         * Produces limited content object for view
         * @method buildViewObject
         * @return {Object:stdClass}
         */
        public function buildViewObject()
        {
            $o = new stdClass();
            $o->formname = self::$formvalue;
            $o->session = $this->_content->session;
            $o->email = $this->_content->email;
            $o->name = $this->_content->name;
            $o->message = $this->_content->message;
            return $o;
        }
        /**
         * Mediator for private object _content
         * @method hasError
         * @param {String} $var
         * @return {Integer} 0 no-error, else class error
         */
        public function hasError($var)
        {
            return $this->_content->hasError($var);
        }
    }
}
