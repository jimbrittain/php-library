<?php
if (!function_exists('produceVCard')) {
    /**
     * @module produceVCard
     * @requires ArgumentHandler, isEmailAddress, isTelephoneNumber, prettifyUKTelephone
     */
    function produceVCard($args)
    {
        function resolveTelephone($n)
        {
            $tels = array();
            if (is_array($n)) {
                foreach ($n as $tel) {
                    if (is_array($tel) && count($tel) > 0) {
                        if (array_key_exists('tel', $tel) && !empty($tel['tel']) && isTelephoneNumber($tel['tel'])) {
                            $t = new stdClass();
                            $t->number = $tel['tel'];
                            //@codingStandardsIgnoreStart - line-length
                            $t->name = (array_key_exists('name', $tel) && !empty($tel['name']) && is_string($tel['name'])) ? $tel['name'] : 'Telephone';
                            //@codingStandardsIgnoreEnd;
                            $tels[] = $t;
                        }
                    } else if (!empty($tel) && isTelephoneNumber($tel)) {
                        $t = new stdClass();
                        $t->name = 'Telephone';
                        $t->number = $tel;
                        $tels[] = $t;
                    }
                }
            } else if (isTelephoneNumber($n)) {
                $t = new stdClass();
                $t->name = 'Telephone';
                $t->number = $n;
                $tels[] =$t;
            }
            return $tels;
        }
        function resolveEmail($e)
        {
            $emails = array();
            if (is_array($e)) {
                foreach ($e as $email) {
                    if (is_array($email) && count($email) > 0) {
                        //@codingStandardsIgnoreStart - line-length
                        if (array_key_exists('email', $email) && !empty($email['email']) && isEmailAddress($email['email'])) {
                        //@codingStandardsIgnoreEnd
                            $t = new stdClass();
                            $t->address = $email['email'];
                            //@codingStandardsIgnoreStart - line-length
                            $t->name = (array_key_exists('name', $email) && !empty($email['name']) && is_string($email['name'])) ? $email['name'] : 'Email';
                            //@codingStandardsIgnoreEnd
                            $emails[] = $t;
                        }
                        continue;
                    } else if (!empty($email) && isEmailAddress($email)) {
                        $t = new stdClass();
                        $t->name = 'Email';
                        $t->address = $email;
                        $emails[] $t;
                        continue;
                    }
                }
            } else if (isEmailAddress($e)) {
                $t = new stdClass();
                $t->name = 'Email';
                $t->address = $e;
                $emails[] = $t;
            }
            return $emails;
        }
        $requriedArguments = array(
            'name' = > '',
            'adr' => '',
            'street-address' => '',
            'locality' => '',
            'region' = > '',
            'country-name' = > '',
            'telephone' => '',
            'email' => '',
            'url' => '',
        );
        $arr = array();
        $a = new ArgumentHandler();
        $a->addHandle($requiredArguments);
        $arr = $a->passArguments($args);
        $str = '';
        $str = '<div class="vcard">';
            $str = $str.'<span class="fn"><a href="/" class="url">'.$arr['name'].'</a></span>';
            if ($arr['adr']) {
                $str = $str.'<address class="adr">';
                    //@codingStandardsIgnoreStart - line-lengths
                    $str = (isset($arr['street-address']) && !empty($arr['street-address'])) ? $str.'<span class="street-address">'.$arr['street-address'].'</span>' : $str;
                    $str = (isset($arr['locality']) && !empty($arr['locality'])) ? $str.'<span class="locality">'.$arr['locality'].'</span>' : $str;
                    $str = (isset($arr['region']) && !empty($arr['region'])) ? $str.'<span class="region">Region</span>' : $str;
                    $str = (isset($arr['country-name']) && !empty($arr['country-name'])) ? $str.'<span class="country-name">Country</span>' : $str;
                    //@codingStandardsIgnoreEnd
               $str = $str.'</address>';
            }
           $str = $str.'<dl>';
                if (count($arr['telephone']) > 0) {
                    foreach ($arr['telephone'] as $tel) {
                        $str = $str.'<dt>'.$tel->name.'</dt>';
                        $str = $str.'<dd><a href="tel:'.$tel->number.'">'.prettifyUKTelephone($tel->number).'</a></dd>';
                    }
                }
                if (count($arr['email']) > 0) {
                    foreach ($arr['email'] as $email) {
                        $str = $str.'<dt>'.$email->name.'</dt>';
                        $str = $str.'<dd><a href="mailto:'.$email->address.'">'.$email->address.'</a></dd>';
                    }
                }
                if (isset($arr['url']) && !empty($arr['url'])) {
                    $str = $str.'<dt>Website</dt>';
                    $str = $str.'<dd><a href="'.$arr['url'].'">'.$arr['url'].'</a></dd>';
                }
           $str = $str.'</dl>';
        $str = $str."</div>";
        return $str;
    }
}
