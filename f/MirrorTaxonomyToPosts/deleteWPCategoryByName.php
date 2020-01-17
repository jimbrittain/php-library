<?php
if (function_exists('deleteWPCategoryByName')) {
    /**
     * @function deleteWPCategoryByName
     * @param {String} $s
     * @return {Boolean}
     * @description 'Wordpress function to remove category by name rather than ID
     *  returns boolean true|false based on success
     */
    function deleteWPCategoryByName($s)
    {
        if (is_string($s) && preg_match('/^[a-z0-9]*(?:-[a-z0-9]+)*$/', $s) !== false) {
            //@codingStandardsIgnoreStart
            $d = get_cat_ID($s);
            //@codingStandardsIgnoreEnd
            if ($d !== 0) {
                return wp_delete_category($d);
            }
        }
        return false;
    }
}
