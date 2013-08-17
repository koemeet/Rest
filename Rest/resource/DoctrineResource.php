<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 7/20/13
 * Time: 1:04 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Resource;

use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rest\Application;
use Rest\Dbal\Doctrine\Bootstrap;
use Rest\Dbal\Doctrine\Serializer\EntitySerializer;
use Rest\Http\Request;
use Rest\Http\Response;
use Rest\Renderer\Template\File;
use Rest\Renderer\Template;
use Rest\Resource;
use Rest\Utils\Convert;

/**
 * Class DoctrineResource
 * @package Rest\Resource
 */
class DoctrineResource extends Resource
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Rest\Dbal\Doctrine\Serializer\EntitySerializer
     */
    protected $serializer;

    /**
     * @var Request
     */
    public $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $doctrine = new Bootstrap($this->application->config);
        $this->em = $doctrine->em;

        $this->application->autoloader->registerNamespace($doctrine->entityNamespace, $this->application->getFullPath() . '/doctrine');

        $this->serializer = new EntitySerializer($this->em);
    }

    /**
     * @param $qb
     * @param array $fields
     * @param $class
     * @param $alias
     * @return mixed
     * @throws \Exception
     */
    private function _joinTables(&$qb, array $fields, $class, $alias)
    {
        // If we only have 1 field left, return the current alias, because there is no more joining to do
        if (count($fields) <= 1)
        {
            return $alias;
        }

        $field = array_shift($fields);

        $metaData = $this->em->getClassMetadata($class);

        // Check if this field is an association field, otherwise throw an error
        if (!$metaData->hasAssociation($field))
        {
            throw new \Exception("Field '" . $field . "' is not a foreign key for entity '" . $class . "'");
        }

        $mapping = $metaData->getAssociationMapping($field);

        $_alias = $field . count($fields);
        $qb->join($alias . '.' . $field, $_alias);

        return $this->_joinTables($qb, $fields, $mapping['targetEntity'], $_alias);
    }

    /**
     * CRUD: Read
     *
     * @override
     */
    public function __read()
    {
        if (!$this->entity) return;

        $args = func_get_args();

        $qb = $this->em->getRepository($this->entity)->createQueryBuilder('t');

        // Where statement
        foreach ($this->request->getQuery() as $field => $value)
        {
            // Strip field to check for associated fields
            $parts = explode('.', $field);
            $alias = $this->_joinTables($qb, $parts, $this->entity, 't');

            $param = $alias . uniqid();
            $field = $alias . '.' . array_pop($parts);

            $qb->andWhere($field . ' = :' . $param);
            $qb->setParameter($param, $value);
        }

        // Order by statement
        foreach ($this->request->getOrderBy() as $field => $order)
        {
            $parts = explode('.', $field);
            $alias = $this->_joinTables($qb, $parts, $this->entity, 't');

            $order = (strtolower($order) == 'd') ? 'DESC' : 'ASC';

            $qb->addOrderBy($alias . '.' . array_pop($parts), $order);
        }

        $qb->setMaxResults($this->request->getMaxResults());
        $qb->setFirstResult($this->request->getOffset());

        $result = $qb->getQuery()->getResult();

        $qb->select('COUNT(t) as total');

        $totalCount = $qb->getQuery()->getSingleScalarResult();

        // If the first argument passed is 'true', return the result array only
        if (isset($args['0']) && $args['0'] === true)
        {
            return $result;
        }

        // Serialize response
        $this->__serialize($result);



        /*
        if ($this->request->getJoin())
        {
            foreach ($result as $k => $item)
            {
                foreach ($this->request->getJoin() as $k => $join)
                {
                    $metaData = $this->em->getClassMetadata($join);
                    $tan = $metaData->getAssociationsByTargetClass($this->entity);

                    foreach ($tan as $t)
                    {
                        $qb = $this->em->getRepository($t['sourceEntity'])->createQueryBuilder('j');
                        $qb->where('j.' . $t['fieldName'] . ' = :targetEntity');
                        $qb->setParameter('targetEntity', $item['id']);

                        $tan = $qb->getQuery()->getResult();

                        if ($tan)
                        {
                            $result[$k]['messages'] = $this->serializer->toArray($tan);
                        }
                    }
                }
            }
        }*/

        $response['totalCount'] = $totalCount;
        $response[$this->getXMLRootElement()] = $result;

        return new Response\XSDResponse(Response::OK, $response, $this);
    }


    /**
     * CRUD: Create
     */
    public function __create()
    {
        $args = func_get_args();
        $responseData = array();

        $postData = $this->_parseInput();
        $this->fixPostdata($postData);

        // Start transaction
        $this->em->beginTransaction();

        foreach ($postData as $item)
        {
            $entity = $this->_processEntity($this->entity, $item);
            $responseData[] = $entity;

            try
            {
                $this->em->persist($entity);
                $this->em->flush();

                $this->em->refresh($entity);

                $this->em->commit();
            }
            catch (\Exception $e)
            {
                $this->em->rollback();

                throw new \Exception($e->getMessage());
            }
        }

        // Serialize response
        $this->__serialize($responseData);

        return new Response\XSDResponse(Response::OK, $responseData, $this, false);
    }


    /**
     * CRUD: Update
     * @return Response\XSDResponse|void
     */
    public function __update()
    {
        $args = func_get_args();
        $responseData = array();

        $postData = $this->_parseInput();
        $this->fixPostdata($postData);

        foreach ($postData as $item)
        {
            if (!array_key_exists('uuid', $item))
            {
                throw new \Exception("When updating an entity, you need to provide an uuid");
            }

            $entity = $this->em->getRepository($this->entity)->findOneBy(array('uuid' => $item['uuid']));

            // We can update this entity, because it exists
            if (!$entity)
            {
                throw new \Exception("There is no '" . $this->entity . "' with the uuid '" . $item['uuid'] . "'");
            }

            $entity = $this->_processEntity($entity, $item);

            try
            {
                $this->em->persist($entity);
                $this->em->flush();

                $this->em->refresh($entity);

                $responseData[] = $entity;
            }
            catch (\Exception $e)
            {
                throw new \Exception($e->getMessage());
            }
        }

        return new Response\XSDResponse(Response::OK, $postData, $this);
    }

    /**
     * Serialize
     *
     * @param array $responseData
     */
    protected function __serialize(array &$responseData)
    {
        $responseData = $this->serializer->toArray($responseData);
    }

    /**
     * Process entity recursively
     *
     * What this class does is it takes a base entity (classname or an entity object itself) and it loops trough the
     * properties of this entity, the associated properties will go to this same method again, but this time with it's
     * own array data. At the end, we will have an fully builded entity with it's child entities in it.
     *
     * When the process of the entity is done, simply call $em->merge($entity). This will
     *
     * @param $entityClass
     * @param array $data
     */
    private function _processEntity($entityClass, array $data)
    {
        // Get class and it's metadata
        $metaData = $this->em->getClassMetadata((is_object($entityClass)) ? get_class($entityClass) : $entityClass);

        $fieldNames         = $metaData->getFieldNames();
        $assocFieldNames    = $metaData->getAssociationNames();

        $entity = (is_object($entityClass)) ? $entityClass : new $entityClass;

        // Normal fields
        foreach ($fieldNames as $field)
        {
            if (!array_key_exists($field, $data))
            {
                continue;
            }

            $fieldInfo = $metaData->getFieldMapping($field);

            $value = $data[$field];

            switch ($fieldInfo['type']) {
                case DateTimeType::DATETIME:
                    $value = \DateTime::createFromFormat("Y-m-d\TH:i:s", $value);

                    if (!$value)
                    {
                        $value = new \DateTime();
                        $value->setTimestamp(0);
                    }

                    break;
            }

            $method = "set" . ucfirst($field);

            if (is_callable(array($entity, $method)))
            {
                $entity->$method($value);
            }
        }

        // Associated fields. This means that those fields wont accept normal values, but Entities instead. This means
        // That we have to create the needed entity or get it if an identifier is specified.
        foreach ($assocFieldNames as $field)
        {
            if (!array_key_exists($field, $data))
            {
                continue;
            }


            $assocMapping = $metaData->getAssociationMapping($field);
            $assocClass = $assocMapping['targetEntity'];

            $value = $data[$field];
            $method = "set" . ucfirst($field);

            // It already exists, lets update this entity then...
            if (isset($value['uuid']))
            {
                $assocEntity = $this->em->getRepository($assocClass)->findOneBy(array('uuid' => $value['uuid']));

                if (!$assocEntity)
                {
                    throw new \Exception("No '" . $assocClass . "' exists with the uuid '" . $value['uuid'] . "'");
                }

                // Again, process this (pre-filled) entity
                $assocEntity = $this->_processEntity($assocEntity, $value);
            }
            // No uuid, so create an empty one
            else
            {
                $assocEntity = new $assocClass;
                $assocEntity = $this->_processEntity($assocEntity, $value);

                $this->em->persist($assocEntity);
                $this->em->flush($assocEntity);
                $this->em->refresh($assocEntity);
            }

            if (is_callable(array($entity, $method)))
            {
                $entity->$method($assocEntity);
            }
        }

        return $entity;
    }

    /**
     * Parse input stream and return it as an array value
     *
     * @return array
     * @throws \Exception
     */
    private function _parseInput()
    {
        $input = trim(file_get_contents("php://input"));

        if (!$input) throw new \Exception("No input");

        if($input[0] == '{' || $input[0] == '[')
        {
            $xml = $this->jsonToXML($input);
        }

        $xml = $this->schema->validate($xml);

        // Convert the XML Document to an usable array
        return (array)Convert::xmlToArray($xml);
    }

    /**
     * @param $json
     * @return string
     * @throws \Exception
     */
    private function jsonToXML($json)
    {
        $templateFile = $this->application->config['xml']['templates_path'] . '/' . $this->getXMLTemplateFile();

        $result = json_decode($json, true);

        if ($result === null)
        {
            throw new \Exception("Input stream is not in a valid JSON format");
        }

        $result = $result[$this->getXMLRootElement()];

        // If array is associative, put it inside an empty array
        if ($this->_isAssoc($result))
        {
            $result = array($result);
        }

        $file = new File($templateFile, $result);
        $template = new Template($file);

        return $template->render();
    }

    /**
     * @param array $array
     * @return bool
     */
    private function _isAssoc(array $array)
    {
        $array = array_keys($array);

        return ($array !== array_keys($array));
    }
}