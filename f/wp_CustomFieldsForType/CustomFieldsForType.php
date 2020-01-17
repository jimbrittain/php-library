<?php
function wordpressValidPostType($type = '')
{
    if (!empty($type) && is_string($type)) {
        if (function_exists('post_type_exists')) {
            return post_type_exists($type);
        } else {
            return true;
        }
    }
    return false;
}

class CustomFieldsForType
{
    public static $active = true;
    public static $collection = array();

    public static function findObjectForType($type, $shouldCreate = true)
    {
        $shouldCreate = (is_bool($shouldCreate)) ? $shouldCreate : true;
        if (wordpressValidPostType($type)) {
            foreach ($collection as $t) {
                if ($t->for === $type) {
                    return $t;
                }
            }
            if ($shouldCreate) {
                self::$collection[] = new CustomFieldsType(
                    array(
                        'type' => $type
                    )
                );
            }
        } else {
            throw new Exception(
                "CustomFieldsForType->findObjectForType supplied
                none-existant/invalid \$type"
            );
        }
    }
}

