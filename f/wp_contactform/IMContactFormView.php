<?php
defined('ABSPATH') or die('');
if (!class_exists('IMContactFormView')) {
    class IMContactFormView
    {
        public $master = '';
        public $snippetsFolder = 'snippets';
        public function __construct(IMContactForm $imcf)
        {
            $this->master = $imcf;
            if (!function_exists('returnSnippetFromArray')) {
                require_once(sprintf("%s/returnSnippetFromArray.php", dirname(__FILE__)));
            }
            $this->snippetsFolder = sprintf("%s/".$this->snippetsFolder."/", dirname(__FILE__));
        }
        public function getViewObject()
        {
            return $this->master->buildViewObject();
        }
        public function form($displayErrors = false)
        {
            $str = "";
            $uri = "";
            if (defined('INPUT_SERVER')) {
                $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
            } else if (isset($_SERVER)) {
                $uri = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : "";
            }
            $str .= '<form name="contact" id="imcontactform" autocomplete="off" method="post" action="'.$uri.'">';
            $str .= $this->formContents($displayErrors);
            $str .= $this->formSubmitButton($displayErrors);
            $str .= '</form>';
            return $str;
        }
        public function warningStart($message = '')
        {
            $str = '<fieldset class="warning">';
            $str .= (!empty($message)) ? '<p><strong>'.$message.'</strong></p>' : '';
            return $str;
        }
        public function warningEnd()
        {
            $str = '</fieldset>';
            return $str;
        }
        public function formContents($displayErrors = false)
        {
            $str = '';
            $o = $this->getViewObject();
            if (function_exists('createSnippetReplacer')) {
                createSnippetReplacer($o);
                $str .= returnSnippetFromArray($this->snippetsFolder.'/hidden.inc');
                $str .= $this->formNameInput(($displayErrors) ? $this->master->hasError('name') : 0);
                $str .= $this->formEmailInput(($displayErrors) ? $this->master->hasError('email') : 0);
                $str .= $this->formMessageInput(($displayErrors) ? $this->master->hasError('message') : 0);
            }
            return $str;
        }
        public function formNameInput($error = 0)
        {
            $obj = $this->getViewObject();
            $str = '';
            $warned = false;
            if ($error !== 0) {
                $warned = true;
                $str .= $this->warningStart();
            }
            $str .= returnSnippetFromArray($this->snippetsFolder.'/name.inc', array(), $obj);
            $str .= ($warned) ? $this->warningEnd() : '';
            return $str;
        }
        public function formEmailInput($error = 0)
        {
            $obj = $this->getViewObject();
            $str = '';
            $warned = false;
            if ($error !== 0) {
                $warned = true;
                $str .= $this->warningStart();
            }
            $str .= returnSnippetFromArray($this->snippetsFolder.'/email.inc', array(), $obj);
            $str .= ($warned) ? $this->warningEnd() : '';
            return $str;
        }
        public function formMessageInput($error = 0)
        {
            $obj = $this->getViewObject();
            $str = '';
            $warned = false;
            if ($error !== 0) {
                $warned = true;
                $str .= $this->warningStart();
            }
            $str .= returnSnippetFromArray($this->snippetsFolder.'/message.inc', array(), $obj);
            $str .= ($warned) ? $this->warningEnd() : '';
            return $str;
        }
        public function formSubmitButton()
        {
            $obj = $this->getViewObject();
            $str = '';
            $str .= returnSnippetFromArray($this->snippetsFolder.'/button.inc', array(), $obj);
            return $str;
        }
        public function overlay($value)
        {
            $obj = $this->getViewObject();
            $str = '';
            switch ($value) {
                case 'submission':
                    $str .= returnSnippetFromArray($this->snippetsFolder.'/overlay-submission.inc', array(), $obj);
                    break;
                case 'inactive':
                    $str .= returnSnippetFromArray($this->snippetsFolder.'/overlay-inactive.inc', array(), $obj);
                    break;
                case 'error':
                    $str .= returnSnippetFromArray($this->snippetsFolder.'/overlay-error.inc', array(), $obj);
                    break;
                case 'inactive':
                default:
                    break;
            }
            return $str;
        }
    }
}
