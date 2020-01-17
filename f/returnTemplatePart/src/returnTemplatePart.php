<?php
    /**
      @module returnTemplatePart
      @method returnTemplatePart
      @param {String} $slug
      @param {String} $name [opt]
      @author ImmatureDawn
      @requires get_template_part (wp)
      @description returns WP get_template_part as a variable;
     */
    function returnTemplatePart($slug, $name = null)
    {
        if (function_exists('get_template_part') && is_string($slug) && (is_string($name) || $name === null)) {
            ob_start();
            get_template_part($slug, $name);
            return ob_get_clean();
        }
    }
