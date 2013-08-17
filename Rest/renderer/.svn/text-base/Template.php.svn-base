<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 8/9/13
 * Time: 9:05 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Renderer;

use Rest\Io\Exception\FileDoesNotExistException;
use Rest\Renderer\Template\File;

/**
 * Class Template
 * @package Rest\Xml
 */
class Template
{
    protected $file;

    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @param bool $return
     * @return string
     * @throws \Rest\Io\Exception\FileDoesNotExistException
     */
    public function render($return = true)
    {
        if (!file_exists($this->file->getPath()))
        {
            throw new FileDoesNotExistException($this->file->getPath());
        }

        ob_start();
        include $this->file->getPath();

        $content = ob_get_contents();
        ob_end_clean();

        if ($return === true)
        {
            return $content;
        }

        echo $content;
    }

    /**
     * Shortcut for creating a new template
     *
     * When you set $relativePath to false, you the $file will be an absolute path.
     *
     * @param $file
     * @param array $data
     * @param bool $relativePath
     * @param bool $return
     * @return string
     */
    public function load($file, $data = null, $return = false, $relativePath = true)
    {
        if ($data === null)
        {
            return null;
        }

        if ($relativePath === true)
        {
            $file = dirname($this->file->getPath()) . '/' . $file;
        }

        $file       = new File($file, $data);
        $template   = new Template($file);

        if ($return === true)
        {
            return $template->render();
        }

        echo $template->render();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->file->getData();
    }

    public function getTotalCount()
    {
        return 222;
    }

    /**
     * @param $property
     * @return null
     */
    public function __get($property)
    {
        $data = $this->file->getData();

        if (array_key_exists($property, $data))
        {
            return $data[$property];
        }

        return null;
    }
}