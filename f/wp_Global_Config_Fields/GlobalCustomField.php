<?php
/*
    @module GlobalCustomField
    @class Global CustomField
    @constructor
    @params name, type, default, (validation)
    #param name may be either a string name, in which case type, and default params are also used,
    or can be an array with name, type, default and validation used.
    Validation is only accessible by using an array.
    @param {String|Array} name
    A 64 character string consisting of /^[0-9a-zA-Z][0-9a-zA-Z\_\-\ ]{0,63}$/
    @param {String} type [optional]
    one of types (String) {text|email|tel|textarea|url|number}
    @param {Function} validation [optional]
    A custom function for validation, else uses one of the defined standards for type
    @param {String} default [optional]
    A default value for the custom field
 */
class GlobalCustomField
{
    public $name = '';
    public $default = '';
    public $type = 'text';
    public $helptext = '';
    public $cachedValue = '';
    public $cached = false;
    public $valid = false;
    
    /*
        @method __construct
     */
    public function __construct ($name, $type = 'text', $default = '')
    {
      $helptext = '';
      if (is_array($name)) {
        if (isset($name['validation']) && is_callable($name['validation'])) {
          $validation = $name['validation'];
        }
        $type = (isset($name['type'])) ? $name['type'] : $type;
        $default = (isset($name['default'])) ? $name['default'] : $default;
        $helptext = (isset($name['helptext'])) ? $name['helptext'] : $helptext;
        //$name must be at end to avoid overiding vals;
        $name = (isset($name['name'])) ? $name['name'] : $name;
      }
      $check = true;
      $holder = globalfield_array();
      $reg = '/^[0-9a-zA-Z][0-9a-zA-Z\_\-\ ]{0,63}$/';
      $check = (preg_match($reg, $name) === false) ? false : $check;
      $type = strtolower($type);
      switch ($type) {
          case 'text':
          case 'email':
          case 'tel':
          case 'textarea':
          case 'url':
          case 'number':
              break;
          default:
              $type = 'text';
              break;
      }
      foreach ($holder as $c) { //dirty check for name
        if ($c->name == $name) {
          $check = false;
        }
      }
      if ($check) {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->helptext = $helptext;
        $this->valid = true;
      }
    }
    public function prefixedName($n = '')
    {
        $n = (empty($n)) ? $this->name : $n;
        if (is_string($n)) {
            $reg = "/^gcf-/";
            if (preg_match($reg, $n)) {
                return $n;
            } else {
                return "gcf-".$n;
            }
        }
    }
    public function getValue()
    {
      if (!$this->cached) {
        $opt = '';
        if (function_exists('get_option')) {
          $opt = get_option($this->prefixedName());
        }
        $this->cached = true;
        $this->cachedValue = $opt;
      }
      return $this->cachedValue;
    }
    public function deleteValue()
    {
        $opt = false;
        if (function_exists('delete_option')) {
            $opt = delete_option($this->prefixedName());
        }
        return $opt;
    }
    public function sanitizeValue($v)
    {
      switch ($this->type) {
        case 'tel':
            $reg = '/^\+[0-9]{1-4}[0-9\-]{8,20}$/';
            $v = (preg_match($reg, $v) !== false) ? $v : '';
            break;
        case 'email':
            $reg = '/^[a-zA-Z0-9]+[a-zA-Z0-9\.\_\-]{0,128}@[a-zA-Z0-9]+[a-zA-Z0-9\.\_\-]{3,128}$/';
            $v = (preg_match($reg, $v) !== false) ? $v : '';
            break;
        case 'number':
            $reg = '/^[0-9\.\-\,]+$/';
            $v = (preg_match($reg, $v) !== false) ? $v : '';
            break;
        case 'text':
        case 'textarea':
        default:
            $v = sanitize_text_field($v);
            break;
      }
      return $v;
    }
    public function setValue($v)
    {
      $t = true;
      $v = $this->sanitizeValue($v);
      if (function_exists('update_option')) {
        $t = update_option($this->prefixedName(), $v);
      }
      if ($t) {
        $this->cachedValue = $t;
        $this->cached = true;
        return true;
      } else {
        return false;
      }
    }
    public function view_editHTML()
    {
      $str = "<fieldset>";
      if (!empty($this->helptext)) { 
          $str = $str."<p>".$this->helptext."</p>"; 
      }
      $str = $str."<label for=\"".$this->prefixedName()."\">Call with {".$this->name."}</label>";
      switch ($this->type) {
        case 'textarea':
            //@codingStandardsIgnoreStart
            $str = $str."<textarea id=\"".$this->prefixedName()."\" name=\"".$this->prefixedName()."\">".esc_attr($this->getValue())."</textarea>";
            //@codingStandardsIgnoreEnd
            break;
        case 'number':
            //@codingStandardsIgnoreStart
            $str = $str."<input type=\"number\" id=\"".$this->prefixedName()."\" name=\"".$this->prefixedName()."\" value=\"".esc_attr($this->getValue())."\" />";
            //@codingStandardsIgnoreEnd
            break;
        case 'email':
            //@codingStandardsIgnoreStart
            $str = $str."<input type=\"email\" id=\"".$this->prefixedName()."\" name=\"".$this->prefixedName()."\" value=\"".esc_attr($this->getValue())."\" />";
            //@codingStandardsIgnoreEnd
            break;
        case 'tel':
            //@codingStandardsIgnoreStart
            $str = $str."<input type=\"tel\" id=\"".$this->prefixedName()."\" name=\"".$this->prefixedName()."\" value=\"".esc_attr($this->getValue())."\" />";
            //@codingStandardsIgnoreEnd
            break;
        case 'url':
            //@codingStandardsIgnoreStart
            $str = $str."<input type=\"url\" id=\"".$this->prefixedName()."\" name=\"".$this->prefixedName()."\" value=\"".esc_attr($this->getValue())."\" />";
            //@codingStandardsIgnoreEnd
            break;
        case 'text':
        default:
            //@codingStandardsIgnoreStart
            $str = $str."<input type=\"text\" id=\"".$this->prefixedName()."\" name=\"".$this->prefixedName()."\" value=\"".esc_attr($this->getValue())."\" />";
            //@codingStandardsIgnoreEnd
            break;
      }
      $str = $str."</fieldset>";
      echo $str;
    }
}
