<?php

if (!class_exists('IMRestrictToPagesSettings')) {
    class IMRestrictToPagesSettings
    {
        public $groupname = 'imrestricttopages-group';
        public $pagename = 'imrestricttopages';
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'adminInit'));
            add_action('admin_menu', array(&$this, 'adminMenu'));
        }
        public function viewHelpText()
        {
            $str = '';
            $str .= '<p>Write out a description, comma or space separated, url types etc.</p>';
            echo $str;
        }
        public function settingsFormView()
        {
            $opt = IMRestrictToPagesPlugin::get();
            $str = "";
            $str .= '<label for="'.IMRestrictToPagesPlugin::$optname.'">Acceptable pages</label>';
            $str .= '<textarea name="'.IMRestrictToPagesPlugin::$optname;
            $str .= '" id="'.IMRestrictToPagesPlugin::$optname.'"';
            $str .= ' placeholder="if blank = front page">';
            $str .= (count($opt->getPages()) > 0) ? implode(', ', $opt->getPages()) : '';
            $str .= '</textarea>';
            echo $str;
        }
        public function adminInit()
        {
            // @codingStandardsIgnoreStart
            register_setting($this->groupname, IMRestrictToPagesPlugin::$optname, array('IMRestrictToPagesPlugin', 'sanitize'));
            // @codingStandardsIgnoreEnd
            add_settings_section(
                'imrestricttopages-template-section',
                'IM Restrict To Pages Settings', 
                array(&$this, 'viewHelpText'),
                $this->pagename
            );
            add_settings_field(
                'imrestricttopages-template-settings', 
                'Pages That Are Acceptable&#8230;', 
                array(&$this, 'settingsFormView'), 
                $this->pagename, 
                'imrestricttopages-template-section', 
                array(
                    'field' => IMRestrictToPagesPlugin::$optname
                )
            );
        }
        
        public function adminMenu()
        {
            add_options_page(
                'Restrict To Pages Plugin Settings',
                'IM Restrict To Page Template', 
                'manage_options', 
                $this->pagename, 
                array(&$this, 'pluginSettingsPage')
            );
        }
        public function pluginSettingsPage()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            } else {
                echo '<div class="wrap">
                    <h2>Restrict To Pages Template</h2>
                        <form action="options.php" method="post">';
                settings_fields($this->groupname);
                do_settings_fields($this->pagename, $this->groupname);
                do_settings_sections($this->pagename);
                submit_button();
                echo '</form></div>';
            }
        }
    }
}