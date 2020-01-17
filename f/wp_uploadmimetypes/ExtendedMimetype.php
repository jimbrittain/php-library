<?php
if (!class_exists('ExtendedMimetype')) {
    class ExtendedMimetype
    {
        public $mimetype = '';
        public $extension = '';
        public function __construct($mime, $ext = null)
        {
            if (is_array($mime) && count($mime) == 2) {
                if (isset($mime['mimetype']) && isset($mime['extension'])) {
                    $ext = $mime['extension'];
                    $mime = $mime['mimetype'];
                } else if (isset($mime[0]) && isset($mime[1])) {
                    $ext = $mime[1];
                    $mime = $mime[0];
                }
            }
            if (is_string($mime)) {
                $this->setMimetype($mime);
            }
            if (is_string($ext)) {
                $this->setExtension($ext);
            }
        }
        public function isValid()
        {
            return (is_string($this->mimetype) && is_string($this->extension));
        }
        public function setMimetype($mime)
        {
            if (is_string($mime)) {
                $this->mimetype = $mime;
                return true;
            }
            return false;
        }
        public function setExtension($ext)
        {
            if (is_string($ext)) {
                $this->extension = $ext;
                return true;
            }
            return false;
        }
    }
}
