<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/20/13
 * Time: 12:45 AM
 * To change this template use File | Settings | File Templates.
 */
namespace Rest\Resource;

interface XSDInterface
{
    public function getTargetXSDFile();
    public function getXMLRootElement();
    public function getXMLTemplateFile();
}