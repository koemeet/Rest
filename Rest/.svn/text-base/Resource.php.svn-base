<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/20/13
 * Time: 12:40 AM
 * To change this template use File | Settings | File Templates.
 */
namespace Rest;

use Rest\Http\Request;
use Rest\Resource\XSDInterface;
use Rest\Xml\Schema;

/**
 * Class Resource
 * @package Rest
 */
abstract class Resource
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Application
     */
    public $application;

    /**
     * @var Schema
     */
    public $schema;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->application = Application::$shared;

        $xsdFile = $this->application->config['xml']['schemas_path'] . '/' . $this->getTargetXSDFile();
        $this->schema = new Schema($xsdFile);
    }

    public function getTargetXSDFile() {}
    public function getXMLRootElement() {}
    public function getXMLTemplateFile() {}

    /**
     * CRUD: Read
     */
    public function __read()
    {
        echo "READ";
    }

    /**
     * CRUD: Create
     */
    public function __create() {}

    /**
     * CRUD: Update
     */
    public function __update() {}

    /**
     * CRUD: Delete
     */
    public function __delete() {}

    /**
     * Fix POST data
     *
     * @param $postData
     * @return array
     */
    protected function fixPostdata(&$postData)
    {
        $postData = $postData[$this->getXMLRootElement()];

        // Check if array is assoc
        function is_assoc(array $a)
        {
            $a = array_keys($a);
            return ($a != array_keys($a));
        }

        if (is_assoc($postData))
        {
            $postData = array($postData);
        }

        return $postData;
    }
}