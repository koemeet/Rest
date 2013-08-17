<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/20/13
 * Time: 12:23 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Http;
use Rest\Application;
use Rest\Utils\Convert;
use Rest\Utils\DOM;

/**
 * Class Response
 * @package Rest\Http
 */
class Response
{
    const OK                    = 200;
    const PAGE_NOT_FOUND        = 404;
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var Application
     */
    protected $_application;

    /**
     * @param $code
     * @param array $data
     */
    public function __construct($code, array $data)
    {
        $this->_application = Application::$shared;

        $this->setResponseCode($code);

        $this->_data = $data;
    }

    /**
     * @return mixed
     */
    public function output()
    {
        ob_end_clean();

        $output = $this->_application->bootstrap->request->getOutput();

        // Encode output data array
        $this->_data = $this->_encode($this->_data);

        switch ($output)
        {
            case 'xml':
                echo $this->toXml();
                break;
            default:
                echo $this->toJson($this->_data);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Data array to XML
     *
     * @return string
     */
    protected function toXml()
    {
        $this->setContentType('text/xml');
        return DOM::arrayToXMLString($this->_data, 'response', true);
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    protected function toJson(array $data)
    {
        $this->setContentType('application/json');
        $json = json_encode($data);

        if (!$json)
        {
            throw new \Exception("Could not convert response data to JSON: " . json_last_error_msg());
        }

        if (isset($_REQUEST['callback']))
        {
            return $_REQUEST['callback'] . '(' . $json . ');';
        }

        return $json;
    }

    /**
     * Perform an html entity decode on all strings in the array
     *
     * @param array $data
     * @return array
     */
    private function _encode(array $data)
    {
        foreach ($data as $k =>$i)
        {
            if (is_array($i))
            {
                $data[$k] = $this->_encode($i);
            }
            else if (is_string($i))
            {
                $data[$k] = html_entity_decode($i);
            }
        }

        return $data;
    }

    /**
     * @param $code
     */
    public function setResponseCode($code)
    {
        header('Response', null, $code);
    }

    /**
     * @param $contentType
     */
    public function setContentType($contentType)
    {
        header("Content-Type: " . $contentType);
    }
}