<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/19/13
 * Time: 11:19 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Http;

/**
 * Class Request
 *
 * @note Don't throw any exceptions inside this class, because they will not get caught!
 * @package Rest\Http
 */
class Request
{
    protected $_requestUri;

    protected $_httpMethod;

    protected $_resource;
    protected $_action;
    protected $_parameters = array();
    protected $_output;

    protected $_maxResults = 10;
    protected $_status;
    protected $_offset = 0;
    protected $_orderBy;
    protected $_query;
    protected $_join;

    protected $_uuid;

    /**
     * Constructor
     *
     * @param string $request[default=null]
     */
    public function __construct($request = null)
    {
        $this->_requestUri = $this->_cleanUri(($request == null) ? $this->_fetchUri($request) : $request);

        $parts = explode('/', $this->_requestUri);

        $this->_httpMethod = $_SERVER['REQUEST_METHOD'];

        $this->_resource    = array_shift($parts);
        $this->_action      = array_shift($parts);
        $this->_parameters  = (array)$parts;

        if (isset($_GET['maxResults']))
            $this->_maxResults = $_GET['maxResults'];

        if (isset($_GET['q']))
            $this->_query = $_GET['q'];

        if (isset($_GET['uuid']))
        {
            $tan = 'uuid:' . urlencode($_GET['uuid']);
            $this->_query .= ($this->_query) ? ',' . $tan : $tan;
        }

        if (isset($_GET['output']))
            $this->_output = $_GET['output'];

        if (isset($_GET['offset']))
            $this->_output = $_GET['offset'];

        if (isset($_GET['orderBy']))
            $this->_orderBy = $_GET['orderBy'];

        if (isset($_GET['join']))
            $this->_join = $_GET['join'];
    }

    public function getHttpMethod()
    {
        return $this->_httpMethod;
    }

    public function getResource()
    {
        return $this->_resource;
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

    public function getMaxResults()
    {
        return $this->_maxResults;
    }

    public function getOffset()
    {
        return $this->_offset;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getJoin()
    {
        $tables = explode(',', $this->_join);

        return $tables;
    }

    /**
     * Get query in following array format:
     *
     *  array(
     *      'field' => 'value'
     *  );
     *
     * @return array
     */
    public function getQuery()
    {
        $data = array();

        $fields = explode(',', $this->_query);

        if ($fields['0'])
        {
            foreach ($fields as $field)
            {
                $parts = explode(':', $field);
                $data[$parts['0']] = (isset($parts['1'])) ? $parts['1'] : '';
            }
        }

        return $data;
    }

    /**
     * Get orderBy in following array format:
     *
     *  array(
     *      'field' => 'A|D'
     *  );
     *
     * @return array
     */
    public function getOrderBy()
    {
        $data = array();

        $fields = explode(',', $this->_orderBy);

        if ($fields['0'])
        {
            foreach ($fields as $field)
            {
                $parts = explode(':', $field);

                if (!isset($parts['1']))
                {
                    $parts['1'] = 'A';
                }

                $data[$parts['0']] = $parts['1'];
            }
        }

        return $data;
    }



    protected function _cleanUri($uri)
    {
        return parse_url(trim(urldecode($uri), '/'), PHP_URL_PATH);
    }

    protected function _fetchUri()
    {
        $uri = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
        if ($uri != '' && $uri != '/')
        {
            return $uri;
        }

        // Pick the REQUEST_URI as last resort
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
        {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        }
        else if (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
        {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        // Fix the $_GET vars
        $parts = explode('?', $uri, 2);
        $uri = $parts['0'];

        return $uri;
    }
}