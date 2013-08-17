<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/19/13
 * Time: 11:24 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest;

/**
 * Class Autoloader
 * @package Rest
 */
class Autoloader
{
    private $_namespaces = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * @param $namespace
     * @param $path
     */
    public function registerNamespace($namespace, $path)
    {
        $this->_namespaces[$namespace] = rtrim(strtolower($path), '/') . '/';
    }

    /**
     * Autoload
     *
     * @param $classname
     * @throws \Exception
     */
    public function autoload($classname)
    {
        $path       = explode('\\', $classname);
        $namespace  = array_shift($path);
        $filename   = array_pop($path);
        $clean_path = $namespace . '/' . strtolower(implode('/', $path)) . '/' . $filename . '.php';

        if (file_exists($clean_path))
        {
            include $clean_path;
            return;
        }
        else
        {
            foreach ($this->_namespaces as $_ns => $_path)
            {
                if ($_ns == $namespace)
                {
                    $file = $_path . $clean_path;

                    if (file_exists($file))
                    {
                        include $_path . $clean_path;
                        return;
                    }
                }
            }
        }

        throw new \Exception('Class ' . $classname . ' could not be found.');
    }
}