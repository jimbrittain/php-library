<?php
if (!class_exists('MultiSrcImage')) {
    require_once('MultiSrcImage.php');
}
if (!class_exists('MultiSrcImageView')) {
    class MultiSrcImageView 
    {
        //needs change name of viewSrcsetType;
        public static function isMultiSrcImage($m = null)
        {
            return (is_a($m, 'MultiSrcImage')) ? true : false;
        }
        public static function viewDescribedByAttr(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('description')) {
                $str = ' aria-describedby="describes'.$m->id().'"';
            }
            return $str;
        }
        public static function viewSources(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b) {
                $types = $m->allTypes();
                while (count($types) > 1) {
                    $type = array_shift($types);
                    $str .= '<source srcset="'.$m->viewSrcsetType($type).'" type="'.$m->getMime($type).'" />'."\n";
                }
            }
            return $str;
        }
        public static function viewFallback(MultiSrcImage $m = null)
        {
            //needs a quick has a fallback method;
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b) {
                $type = $m->fallbackType();
                $str .= '<img src="'.$m->defaultSrc($type).'"';
                $str .= (count($m->allOfType($type)) > 1) ? ' srcset="'.$m->viewSrcsetType($type, false).'"' : '';
                $str .= self::viewAltAttr($m);
                $str .= self::viewLongDescAttr($m);
                $str .= ' />';
            }
            return $str;
        }
        /**
         * @method viewLongDescAttr
         * @param MultSrcImage
         * @return {String}
         * @description returns the longdesc attribute;
         **/
        public static function viewLongDescAttr(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('description')) {
                $str = ' longdesc="#describes-'.$m->get('id').'"';
            }
            return $str;
        }
        public static function viewIdAttr(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('description')) {
                $str = ' id="'.$m->get('id').'"';
            }
            return $str;
        }
        public static function viewTitleAttr($m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('title')) {
                $str = ' title="'.urlencode($m->get('title')).'"';
            }
            return $str;
        }
        public static function viewLink(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('link')) {
                $str .= '<a href="'.$m->get('link').'"';
                if ($m->has('linkAttrs')) {
                    $r = $m->get('linkAttrs');
                    foreach ($r as $key => $value) {
                        $str .= ' '.$key.'="'.$value.'"';
                    }
                }
                $str .= '>';
            }
            return $str;
        }
        public static function viewAltAttr(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('alt')) {
                return ' alt="'.urlencode($m->get('alt')).'"';
            }
            return $str;
        }
        public static function viewPicture(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('multipleTypes')) {
                $str .= '<picture ';
                $str .= self::viewDescribedByAttr($m);
                $str .= self::viewTitleAttr($m);
                $str .= '>';
                    $str .= self::viewSources($m);
                    $str .= self::viewFallback($m);
                $str .= '</picture>';
            }
            return $str;
        }
        public static function viewDescription(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            if ($b && $m->has('description')) {
                $str .= '<figcaption id="describes-'.$m->id().'">'.$m->get('description').'</figcaption>';
            }
            return $str;
        }
        public static function view(MultiSrcImage $m = null)
        {
            $str = '';
            $b = (self::isMultiSrcImage($m)) ? true : false;
            $str .= ($m->has('description')) ? '<figure>' : '';
                if ($m->has('link')) {
                    $str .= self::viewLink($m);
                }
                    if ($m->has('multipleTypes')) {
                        $str .= self::viewPicture($m);
                    } else {
                        $str .= self::viewFallback($m);
                    }
                $str .= ($m->has('link')) ? '</a>' : '';
                if ($m->has('description')) {
                    $str .= self::viewDescription($m).'</figure>';
                }
            return $str;
        }
    }
}
