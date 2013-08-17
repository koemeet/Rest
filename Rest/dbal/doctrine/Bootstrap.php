<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Steffen
 * Date: 27-6-13
 * Time: 15:43
 * To change this template use File | Settings | File Templates.
 */
namespace Rest\Dbal\Doctrine;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Rest\Application;

/**
 * Class Boostrap
 * @package Rest\Dbal\Doctrine
 */
class Bootstrap
{
    /**
     * @var EntityManager
     */
    public $em;

    /**
     * @var string
     */
    public $entityNamespace;

    /**
     * @var array
     */
    public $config = array();

    /**
     * Constructor
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        require_once "vendor/autoload.php";

        $this->entityNamespace = $this->config['doctrine']['entity_namespace'];

        $paths      = array($this->entityNamespace);
        $isDevMode  = true;

        $dbConfig   = array(
            'driver'    => $this->config['database']['driver'],
            'user'      => $this->config['database']['user'],
            'password'  => $this->config['database']['password'],
            'dbname'    => $this->config['database']['dbname']
        );

        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
        $config->setMetadataCacheImpl(new ArrayCache());
        //$config->setQueryCacheImpl(new ApcCache);

        $this->em = EntityManager::create($dbConfig, $config);
    }
}