<?php

if (!class_exists('UserFormSettings')) {
    class UserFormSettings
    {
        public $groupname = 'userform-group';
        public $pagename = 'userform';
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'adminInit'));
            add_action('admin_menu', array(&$this, 'adminMenu'));
        }
        public function viewHelpText()
        {
            $str = '';
            $str .= '<p>Email address to receive the user form submissions, 
                      blank sends to the wordpress administrator.</p>';
            echo $str;
        }
        public function settingsFormView()
        {
            $opt = UserFormPlugin::getTo();
            $str = "";
            //@codingStandardsIgnoreStart
            $str .= '<label for="'.UserFormPlugin::$optname.'">Email Address for contact (blank = wp default admin email)</label>';
            //@codingStandardsIgnoreEnd
            $str .= '<input name="'.UserFormPlugin::$optname;
            $str .= '" id="'.UserFormPlugin::$optname.'"';
            $str .= ' placeholder="(wp default admin) email"';
            $str .= ' value="'.$opt.'">';
            echo $str;
        }
        public function adminInit()
        {
            // @codingStandardsIgnoreStart
            register_setting($this->groupname, UserFormPlugin::$optname, array('UserFormPlugin', 'sanitize'));
            // @codingStandardsIgnoreEnd
            add_settings_section(
                $this->pagename.'-template-section',
                'User Form Settings', 
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
                    'field' => UserFormPlugin::$optname
                )
            );
        }
        public function adminMenu()
        {
            add_options_page(
                'Contact Form Plugin Settings',
                'User Form Options', 
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
                    <h2>User Form</h2>
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
