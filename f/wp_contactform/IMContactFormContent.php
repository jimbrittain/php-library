<?php
if (!class_exists('IMContactFormContent')) {
    class IMContactFormContent 
    {
        public $name;
        public $email;
        public $message;
        public $session;
        
        public function findAll($method)
        {
            $this->find($method, 'name');
            $this->find($method, 'email');
            $this->find($method, 'message');
            $this->find($method, 'session');
        }
        public function getPHPValidation($identifier)
        {
            switch ($identifier) {
                case 'email':
                    return 'email';
                case 'name':
                    return 'string';
                case 'message':
                    return 'string';
                case 'session':
                    return '';
            }
        }
        
        public function find($method, $identifier)
        {
            $method = ($method === 'post' || $method === 'get') ? $method : 'post';
            if (defined('INPUT_POST') || defined('INPUT_GET')) {
                // @codingStandardsIgnoreStart
                $this->{$identifier} = filter_input((($method === 'get') ? INPUT_GET : INPUT_POST), $this->getPHPValidation($identifier));
                // @codingStandardsIgnoreEnd
            } else {
                $this->{$identifier} = ($method === 'post') ? $_POST[$identifier] : $_GET[$identifier];
            }
            $this->sanitize($identifier);
        }
        public function sanitize($identifier)
        {
            if (function_exists('sanitize_text_field')) {
                $this->{$identifier} = sanitize_text_field($this->{$identifier});
            } else {
                //You need to add shit here in case of sanitize;
            }
        }
        public function hasError($identifier)
        {
            switch ($identifier) {
                case 'name':
                    return (isFullName($this->name)) ? 0 : 1;
                case 'email':
                    return (isEmailAddress($this->email)) ? 0 : 1;
                case 'message':
                    return ((is_string($this->message)) && strlen($this->message) > 3) ? 0: 1;
                case 'session':
                    return (validateTTKey($this->session, 'contact')) ? 0 : 1;
            }
        }
        public function isValid($identifier)
        {
            $error = false;
            $error = (!$error && $this->hasError('name') !== 0) ? true : false;
            $error = (!$error && $this->hasError('email') !== 0) ? true : false;
            $error = (!$error && $this->hasError('message') !== 0) ? true : false;
            $error = (!$error && $this->hasError('session') !== 0) ? true : false;
            return $error;
        }
    }
}