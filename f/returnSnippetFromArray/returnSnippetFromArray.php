<?php

/**
 * @returnSnippetsFromArray
 * Uses a html inclusion file with the ability to refer to an object which then rewrites the html
 * based on the declarations. Returns the string for user/developer output. Should not interefere
 * with the $objectForReplacement
 * @param {String} $file path to snippet file
 * @param {Array} $lineNumberArray array, if specified any line number starting at 0
 *      that is specified in the array as false, i.e. array[0] = false will not be included in the output
 * @param {Array|Object} $objectForReplacement, if not specified, uses current global, must be an array or
 *      object. Array will be used but changed to ArrayObject (does not effect original). Object will be 
 *      cloned so no chance of collision. Snippet should refer to <?= x ?> where x is the property to be referenced
 *      multi-dimensional allowed by x->y
 * @return {String} does not self output, must be echoed.
 */

if (!function_exists('returnSnippetFromArray')) {
    function returnSnippetFromArray($file, $lineNumberArray = array(), $objectForReplacement = '')
    {
        $str = '';
        if (!empty($objectForReplacement)) {
            $t = createSnippetReplacer($objectForReplacement);
        }
        if (!isset($GLOBALS['imSnippetReplacer'])) {
            // @codingStandardsIgnoreStart
            trigger_error('Custom function returnSnippetsFromArray could not complete, as the Replacement Object was not valid, must be array or object.', E_USER_ERROR);
            // @codingStandardsIgnoreEnd
            return '';
        }
        $handle = fopen($file, 'r');
        if (!$handle) {
            // @codingStandardsIgnoreStart
            trigger_error('Custom function returnSnippetsFromArray could not complete, as the snippet file could not be opened.', E_USER_ERROR);
            // @codingStandardsIgnoreEnd
            return '';
        }
        $i = 0;
        while (($buffer = fgets($handle, 4096)) != false) {
            if (!isset($lineNumberArray[$i]) || $lineNumberArray[$i]) {
                //preg_replace doesn't work, need a one to one match;
                //this could be extended to allow function calls;
                //need to investigate multilevel array effects and whether to flatten;
                //could have a sanitizer e.g. is_string, or can be string on isset;
                $buffer = preg_replace_callback(
                    '#\<\?\=\ ([A-Za-z0-9(\-\>)]+)\ \?\>#',
                    create_function(
                        '$match', 
                        'global $imSnippetReplacer;
                        $loc = "imSnippetReplacer->";
                        $ref = $loc.$match[1];
                        $erf = @eval("return \$$ref;");
                        if(isset($erf)){ return $erf; } else {return ""; };'
                    ), $buffer
                );
                $str = $str.$buffer;
            }
            $i += 1;
        }
        if (!feof($handle)) {
            echo "";
        }
        fclose($handle);
        return $str;
    }
}
if (!function_exists('createSnippetReplacer')) {
    function createSnippetReplacer($a = '')
    {
        $GLOBALS['imSnippetReplacer'] = array();
        if (is_array($a) && count($a) > 0) {
            $GLOBALS['imSnippetReplacer'] = new ArrayObject($a, ArrayObject::ARRAY_AS_PROPS);
        } else if (is_object($a)) {
            $GLOBALS['imSnippetReplacer'] = clone $a;
        } else {
            return false;
        }
    }
}