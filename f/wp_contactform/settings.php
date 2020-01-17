<?php

if (!class_exists('IMContactFormSettings')) {
    class IMContactFormSettings
    {
        public $groupname = 'imcontactform-group';
        public $pagename = 'imcontactform';
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'adminInit'));
            add_action('admin_menu', array(&$this, 'adminMenu'));
        }
        public function viewHelpText()
        {
            $str = '';
            $str .= '<p>Email address to receive the contact form submissions, 
                      blank sends to the wordpress administrator.</p>';
            echo $str;
        }
        public function settingsFormView()
        {
            $opt = IMContactFormPlugin::getTo();
            $str = "";
            $str .= '<label for="'.IMContactFormPlugin::$optname.'">Acceptable pages</label>';
            $str .= '<input name="'.IMContactFormPlugin::$optname;
            $str .= '" id="'.IMContactFormPlugin::$optname.'"';
            $str .= ' placeholder="if blank = wp default admin email"';
            $str .= ' value="'.$opt.'">';
            echo $str;
        }
        public function adminInit()
        {
            // @codingStandardsIgnoreStart
            register_setting($this->groupname, IMContactFormPlugin::$optname, array('IMContactFormPlugin', 'sanitize'));
            // @codingStandardsIgnoreEnd
            add_settings_section(
                $this->pagename.'-template-section',
                'IM Contact Form Settings', 
                array(&$this, 'viewHelpText'),
                $this->pagename
            );
            add_settings_field(
                $this->pagename.'-template-settings', 
                'Email Address', 
                array(&$this, 'settingsFormView'), 
                $this->pagename, 
                $this->pagename.'-template-section', 
                array(
                    'field' => IMContactFormPlugin::$optname
                )
            );
        }
        
        public function adminMenu()
        {
            add_options_page(
                'Contact Form Plugin Settings',
                'IM Contact Form Template', 
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
                    <h2>IM Contact Form</h2>
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