<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 8/9/13
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Helper;

/**
 * Class Xml
 * @package Rest\Helper
 */
class Xml
{
    public static function element($name, $value, $default = null)
    {
        if ($value !== null)
        {
            return '<' . $name . '>' . $value . '</' . $name . '>';
        }
        else if ($default !== null)
        {
            return '<' . $name . '>' . $default . '</' . $name . '>';
        }

        return '';
    }
}