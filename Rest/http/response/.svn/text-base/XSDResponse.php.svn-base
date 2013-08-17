<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/23/13
 * Time: 4:31 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Http\Response;

use Rest\Http\Response;
use Rest\Renderer\Template;
use Rest\Resource;
use Rest\Utils\Convert;
use Rest\Xml\Renderer;
use Rest\Xml\Schema\Validator;
use Rest\Xml\Schema;

/**
 * Class XSDResponse
 * @package Rest\Http\Response
 */
class XSDResponse extends Response
{
    protected $_targetXSDFile;
    protected $_xmlRootElement;
    protected $_xmlTemplateFile;

    private $_specialKeys = array();

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * Constructor
     *
     * @param $code
     * @param array $content
     */
    public function __construct($code, array $content, Resource $resource, $validate = true)
    {
        parent::__construct($code, $content);

        $this->_targetXSDFile = $this->_application->config['xml']['schemas_path'] . '/' . $resource->getTargetXSDFile();
        $this->_xmlTemplateFile = $this->_application->config['xml']['templates_path'] . '/' . $resource->getXMLTemplateFile();

        $this->schema = new Schema($this->_targetXSDFile);

        $file       = new Template\File($this->_xmlTemplateFile, $this->_data);
        $template   = new Template($file);

        $xmlContent = $template->render();

        $this->schema->dom->loadXML($xmlContent);

        // Validate
        if ($validate === true && !$this->schema->validate($xmlContent))
        {
            return;
        }

        $this->_body = $this->schema->dom->saveXML();
    }

    /**
     * Response to XML
     */
    protected function toXml()
    {
        $this->setContentType('application/xml; charset=utf-8');
        echo $this->_body;
    }

    /**
     * Response to JSON
     */
    protected function toJson(array $data)
    {
        $data = Convert::xmlToArray($this->schema->dom);

        return parent::toJson($data);
    }

}