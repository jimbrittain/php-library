<?php
class Gallery
{
}

class GalleryStub
{
    public $name = '';
    public $offset = 0;
    protected $_valid = false;
    public function __construct($name, $offset = 0)
    {
    }
    public function isValid()
    {
        return $this->_valid;
    }
    protected function setName($name)
    {
        if (!empty($name) && is_string($name)) {
            $this->name = $name;
            if (class_exists('Galleries')) {
                $galleries = Galleries::instance();
                if ($galleries->isNamedGallery($this->name)) {
                    $this->valid = true;
                }
            }
        } else {
            return false;
        }
    }
    protected function setOffset($offset = 0)
    {
        if (is_numeric($offset) && (int) $offset === $offset && $offset > -1) {
            $this->offset = $offset;
            return true;
        } else {
            $this->offset = 0;
            return false;
        }
    }
}

final class Galleries
{
    private $_creatable = true;
    protected $_store = array();

    public static function instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Galleries();
        }
        return $inst;
    }
    public function addGallery($name, $args)
    {
        if ($this->_creatable) {
        }
    }
    public static function lockGalleries()
    {
        $galleries = Galleries::instance();
        $galleries->disableGalleryCreation();
    }
    public function disableGalleryCreation()
    {
        $this->_creatable = false;
    }
    public function isNamedGallery($name)
    {
        for ($i=0; $i < count($this->_store); $i+=1) {
            if ($this->_store[$i]->name === $name) {
                return true;
            }
        }
        return false;
    }
    private function __construct()
    {
        if (function_exists('add_action')) {
            add_action('wp_loaded', 'Galleries::lockGalleries');
        }
    }
}
