<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/19/13
 * Time: 11:16 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Rest\Core;
use Rest\Application;
use Rest\Autoloader;
use Rest\Error;
use Rest\Http\Request;
use Rest\Http\Response;
use Rest\Resource;

/**
 * Class Bootstrap
 * @package Rest\Core
 */
class Bootstrap
{
    /**
     * @var \Rest\Application
     */
    public $application;


    /**
     * @var Request
     */
    public $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->application = Application::$shared;

        // Create Request object
        $this->createRequest();

        // Set custom error handler, so that it will always throw an exception
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        $error = new Error();
        set_exception_handler(array($error, 'displayException'));
    }

    /**
     * Create Request object
     *
     * @note Don't throw any exceptions inside this method, they will not get caught!
     */
    public function createRequest()
    {
        $this->request = new Request();
    }

    /**
     * Dispatch request
     */
    public function dispatch()
    {
        $this->__beforeDispatch();

        $action = null;

        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $action = '__read';
                break;
            case 'POST':
                $action = '__create';
                break;
            case 'PUT':
                $action = '__update';
                break;
            case 'DELETE':
                $action = '__delete';
                break;
            case 'OPTIONS':


                if (!$this->request->getResource())
                {
                    echo '<?xml version="1.0" encoding="UTF-8"?>
                        <qa>
                            <link href="/Members" title="Members"/>
                            <link href="/Auth" title="Auth"/>
                        </qa>';
                   die;
                }
                else
                {
                    $dom = new \DOMDocument("1.0", "utf-8");
                    $dom->formatOutput = true;
                    $dom->preserveWhiteSpace = false;
                    $dom->load($this->application->config['xml']['schemas_path'] . '/services.xsd');

                    $xpath = new \DOMXPath($dom);
                    $test = $xpath->query('//xsd:complexType[@name="members"]')->item(0)->childNodes->item(0)->childNodes->item(0);
                    $type = $test->attributes->getNamedItem('type')->nodeValue;

                    $tan = $xpath->query('//xsd:complexType[@name="' . $type . '"]')->item(0);

                    header("Content-Type: text/xml");
                    header("Allow: GET, POST, PUT, DELETE, OPTIONS");
                    echo $dom->saveXML($tan);

                    die;
                }
                break;
        }

        // If there is a custom action, use that one instead
        if ($this->request->getAction())
        {
            $action = $this->request->getAction();
        }

        $class = $this->application->config['application']['namespace'] . '\\Resources\\' . $this->request->getResource();

        $obj = new $class;
        $obj->request = $this->request;

        $result = call_user_func_array(array($obj, $action), $this->request->getParameters());

        $this->__afterDispatch();

        if ($result instanceof Response)
        {
            $result->output();
        }
    }

    /**
     * Events
     */
    protected function __beforeDispatch() {}
    protected function __afterDispatch() {}
}