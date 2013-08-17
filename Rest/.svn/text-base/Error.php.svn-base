<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/20/13
 * Time: 12:02 AM
 * To change this template use File | Settings | File Templates.
 */
namespace Rest;
use Rest\Http\Response;

/**
 * Class Error
 * @package Rest
 */
class Error
{
    protected $application;

    public function __construct()
    {
        $this->application = Application::$shared;
    }

    public function displayException(\Exception $e)
    {
        if ($this->application->config['application']['debug'])
        {
            echo trim($e->getMessage()) . ' (' . $e->getFile() . ':' . $e->getLine() . ')' . PHP_EOL. PHP_EOL;
            print_r($e->getTraceAsString());
            die;
        }

        ob_clean();

        $error = array(
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        );

        $response = new Response(Response::INTERNAL_SERVER_ERROR, array('error' => $error));
        $response->output();
    }
}