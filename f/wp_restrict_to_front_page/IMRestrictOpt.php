<?php

if (!class_exists('IMRestrictOpt')) {
    class IMRestrictOpt
    {
        private $_pages;
        public $enabled = true;
        public function __construct($p = '')
        {
            $this->_pages = array();
            if (!empty($p)) {
                $m = IMRestrictToPagesPlugin::get();
                $this->enabled = ($m->enabled) ? true : false;
                $this->buildPages($p);
            }
        }
        public function getPages()
        {
            return $this->_pages;
        }
        public function processPageString($p = '')
        {
            $tempPages = array();
            if (is_string($p) && !empty($p)) {
                $matches = array();
                preg_match('/([a-zA-Z0-9\%\/\-\_\.\:]+)/', $p, $matches);
                array_shift($matches);
                $tempPages = $matches;
            } else if (is_array($p)) {
                $tempPages = $p;
            }
            return $tempPages;
        }
        public function buildPages($p = '')
        {
            $this->_pages = array();
            $allPages = $this->processPageString($p);
            foreach ($allPages as $a) {
                $this->appendPage($a);
            }
            if (count($this->_pages) === 0) {
                $this->_pages[] = '';
            }
            return true;
        }
        public function appendPage($p = '')
        {
            if (!empty($p) && is_string($p)) {
                if (isAbsoluteUrl($p)) {
                    $p = changeAbsoluteToWPAbsoluteUrl($p);
                } else if (isRelativeUrl($p)) {
                    $p = changeRelativeToWPAbsoluteUrl($p);
                } else {
                    $p = "";
                }
                if (!empty($p) && !$this->isInPages($p)) {
                    $this->_pages[] = $p;
                    return true;
                }
            } else {
                if (!$this->isInPages()) {
                    $this->_pages[] = '';
                    return true;
                }
            }
            return false;
        }
        public function isInPages($t = '')
        {
            $t = (is_string($t)) ? $t : '';
            $is = false;
            foreach ($this->_pages as $p) {
                if (empty($t) && empty($p)) {
                    $is = true;
                    break;
                } else if (!empty($t) && $t == $p) {
                    $is = true;
                    break;
                }
            }
            return $is;
        }
    }
}


function isRelativeUrl($u)
{
    if (is_string($u) && !empty($u)) {
        $reg = '';
        $match = preg_match($reg, $u);
        return ($match !== false) ? true : false;
    }
}
function changeRelativeToAbsoluteUrl($u)
{
    if (is_string($u) && !empty($u) && isRelativeUrl($u)) {
        return site_url($u); //need to fix this without site_url;
    }
}
function isAbsoluteUrl($u)
{
    if (is_string($u) && !empty($u)) {
        // @codingStandardsIgnoreStart
        $siteUrl = (function_exists('site_url')) ? site_url() : filter_input(INPUT_SERVER, 'REQUEST_URI', 'validate_url');
        // @codingStandardsIgnoreEnd
        $siteUrl = stripProtocolsFromUrl($siteUrl);
        $u = stripProtocolsFromUrl($u);
        $reg = '/^'.addslashes($siteUrl).'/';
        if (preg_match($reg, $u) !== false) {
            return true;
        }
    }
    return false;
}
function changeAbsoluteToWPAbsoluteUrl($u)
{
    if (is_string($u) && !empty($u) && isAbsoluteUrl($u)) {
        $u = stripProtocolsFromUrl($u);
        $siteUrl = stripProtocolsFromUrl(site_url());
        $u = substr($u, strlen($siteUrl));
        return site_url($u);
    }
}

function stripProtocolsFromUrl($u)
{
    if (is_string($u) && !empty($u)) {
        $httpMatches = array();
        $httpReg = '/^((http\:\/\/)|(https\:\/\/))?(www\.)?/';
        preg_match($httpReg, $u, $httpMatches);
        if (count($httpMatches) > 0) {
            for ($i=2; $i < count($httpMatches); $i += 1) {
                $u = substr($u, strlen($httpMatches[$i]));
            }
        }
        return $u;
    } else {
        return "";
    }
}