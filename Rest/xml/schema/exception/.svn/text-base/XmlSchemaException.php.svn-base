<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 8/1/13
 * Time: 1:17 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Xml\Schema\Exception;

class XmlSchemaException extends \Exception
{
    public function __construct(array $errors, $code = 0, \Exception $previous = null)
    {
        parent::__construct($errors['0']->message, $code, $previous);
    }
}