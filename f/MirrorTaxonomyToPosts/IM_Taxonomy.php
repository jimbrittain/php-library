<?php
defined('ABSPATH') or die('');
if (!class_exists('IM_Taxonomy')) {
    class IM_Taxonomy
    {
        static function validate($s)
        {
            $preg = '/^[a-z0-9]*(?:-[a-z0-9]+)*$/';
            return (is_string($s) && preg_match($preg, $s) !== false);
        }
        static function validateTaxonomy($tax)
        {
            $preg = '/^[A-Za-z0-9]*(?:-[A-Za-z0-9]+)*$/';
            return (is_string($tax) && preg_match($preg, $tax) !== false);
        }
        static function sanitizeTaxonomy($tax)
        {
            $tax = (self::validateTaxonomy($tax)) ? $tax : 'category';
            $alltaxes = get_taxonomies(array(), 'objects');
            foreach ($alltaxes as $t) {
                //@codingStandardsIgnoreStart
                if ($t->name === $tax || $t->label === $tax) {
                    //@codingStandardsIgnoreEnd
                    return $t->name;
                }
            }
            //this is dangerous, as 'category' may not exist but better than empty;
            return 'category';
        }
        static function termsExist($s, $tax)
        {
            $s = (is_string($s)) ? array($s) : $s;
            $r = array();
            if (is_array($s)) {
                foreach ($s as $slug) {
                    if (is_string($slug) && self::exists($slug, $tax)) {
                        $r[] = $slug;
                    }
                }
            }
            return $r;
        }
        static function exists($s, $tax)
        {
            $tax = (self::taxonomyExists($tax)) ? $tax : '';
            $s = (self::validate($s)) ? sanitize_term_field('slug', $s, 0, $tax, 'db') : '';
            if ($tax === '' || $s === '') {
                return false;
            } else {
                $tax = self::sanitizeTaxonomy($tax);
                $r = self::getID($s, $tax);
                return ($r === 0) ? false : true;
            }
        }
        static function create($s, $tax = 'category', $args = '')
        {
            $tax = (self::taxonomyExists($tax)) ? $tax : '';
            $tax = ($tax !== '') ? self::sanitizeTaxonomy($tax) : '';
            $s = (self::validate($s)) ? sanitize_term_field('slug', $s, 0, $tax, 'db') : '';
            if ($tax === '' || $s === '') {
                return false;
            } else {
                if (!self::exists($s, $tax)) {
                    if (is_array($args)) {
                        //may need to validate args here;
                        $r = wp_insert_term($s, $tax, $args);
                    } else {
                        $r = wp_insert_term($s, $tax);
                    }
                    return (is_wp_error($r)) ? false : true;
                } else {
                    return false;
                }
            }
        }
        static function delete($s, $tax = 'category')
        {
            $tax = (self::taxonomyExists($tax)) ? $tax : '';
            $tax = ($tax !== '') ? self::sanitizeTaxonomy($tax) : '';
            $s = (self::validate($s)) ? sanitize_term_field('slug', $s, 0, $tax, 'db') : '';
            if ($tax === '' || $s === '') {
                return false;
            } else {
                if (self::exists($s, $tax)) {
                    $r = wp_delete_term(self::getID($s, $tax), $tax);
                    return (is_wp_error($r)) ? false : $r;
                } else {
                    return false;
                }
            }
        }
        static function getID($s, $tax)
        {
            $tax = (self::taxonomyExists($tax)) ? $tax : '';
            $s = (self::validate($s)) ? sanitize_term_field('slug', $s, 0, $tax, 'db') : '';
            if ($tax === '' || $s === '') {
                return 0;
            } else {
                $tax = self::sanitizeTaxonomy($tax);
                $r = term_exists($s, $tax);
                if ($r === null || $r === 0) {
                    return 0;
                } else if (is_numeric($r) && $r === (int) $r) {
                    return $r;
                } else if (isset($r['term_taxonomy_id'])) {
                    return $r['term_taxonomy_id'];
                } else {
                    return 0;
                }
            }
        }
        static function taxonomyExists($tax)
        {
            $tax = (self::validateTaxonomy($tax)) ? $tax : '';
            if ($tax === '') {
                return false;
            } else {
                $alltaxes = get_taxonomies(array(), "objects");
                foreach ($alltaxes as $t) {
                    //@codingStandardsIgnoreStart
                    if ($t->name === $tax || $t->label === $tax) {
                        //@codingStandardsIgnoreEnd
                        return true;
                    }
                }
                return false;
            }
        }
    }
}
