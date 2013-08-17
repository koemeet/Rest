<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 8/9/13
 * Time: 9:08 AM
 * To change this template use File | Settings | File Templates.
 */
namespace Rest\Renderer\Template;

/**
 * Class File
 * @package Rest\Renderer
 */
class File
{
    protected $path;
    protected $data = array();

    /**
     * @param $path
     * @param array $data
     */
    public function __construct($path, array $data = array())
    {
        $this->path = $path;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}