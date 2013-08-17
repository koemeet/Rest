<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 8/1/13
 * Time: 3:20 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Xml;

use Rest\Xml\Schema\Exception\XmlSchemaException;

/**
 * Class Schema
 * @package Rest\Xml
 */
class Schema
{
    private $_xmlSchemaFile;

    /**
     * @var \DOMDocument
     */
    public $dom;

    /**
     * Constructor
     *
     * @param $xmlSchemaFile
     */
    public function __construct($xmlSchemaFile)
    {
        libxml_use_internal_errors(true);

        $this->_xmlSchemaFile = $xmlSchemaFile;

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xml->encoding = 'UTF-8';

        $this->dom = $xml;
    }

    /**
     * Validate XML against XML Schema
     *
     * @param $xml
     * @return \DOMDocument
     */
    public function validate($xml)
    {
        $this->dom->loadXML($xml);

        if (!$this->dom->schemaValidate($this->_xmlSchemaFile))
        {
            $errors = (array)libxml_get_errors();

            throw new XmlSchemaException($errors);
        }

        return $this->dom;
    }
}