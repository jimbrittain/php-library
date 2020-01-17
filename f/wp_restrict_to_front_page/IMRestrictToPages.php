<?php

//require list;



if (!class_exists('IMRestrictToPages')) {
    class IMRestrictToPages
    {
        public $pages;
        public function __construct($p = '')
        {
            $this->pages = array();
            $this->usePages($p);
        }
        public function usePages($p = '')
        {
            if (is_a($p, 'IMRestrictOpt')) {
                $this->pages = $p->getPages();
            }
        }
        public function isAdminPage()
        {
            if (function_exists('is_admin') && function_exists('is_login_page')) {
                return (!is_admin() && !is_login_page()) ? false : true;
            }
            return false;
        }
        public function currentPageIsInPages()
        {
            $imax = count($this->pages);
            $found = false;
            $i = 0;
            $currentUrl = filter_input(INPUT_SERVER, 'REQUEST_URI', 'validate_url');
            while (!$found && $i < $imax) {
                if ($currentUrl == $this->pages[$i]) {
                    $found = true;
                    break;
                } else {
                    $i += 1;
                }
            }
            return $found;
        }
        
        public function isPageOkay()
        {
            if (!$this->isAdminPage()) {
                if (count($this->pages) == 0 && is_front_page()) {
                    return true;
                } else if ($this->currentPageIsInPages()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        public function restrict()
        {
            if (!$this->isPageOkay()) {
                $this->redirect();
            }
        }
        public function redirect()
        {
            if (function_exists('wp_redirect')) {
                wp_redirect(home_url('index.php'));
                exit;
            }
        }
    }
}