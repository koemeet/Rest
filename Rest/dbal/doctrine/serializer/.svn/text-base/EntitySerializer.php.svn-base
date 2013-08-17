<?php

namespace Rest\Dbal\Doctrine\Serializer;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\Common\Util\Inflector,
    Doctrine\ORM\EntityManager,
    Exception;

/**
 * Class EntitySerializer
 * @package Bgy\Doctrine
 */
class EntitySerializer
{

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;

        return $this;
    }

    protected function _serializeEntity($entity)
    {
        $data = array();

        if (is_array($entity))
        {
            foreach ($entity as $k => $v)
            {
                $data[$k] = $this->_serializeEntity($v);
            }
        }
        else if (is_object($entity))
        {
            $className = get_class($entity);
            $metadata = $this->_em->getClassMetadata($className);

            foreach ($metadata->fieldMappings as $field => $mapping)
            {
                // We use the public method, because we will otherwise get empty data
                $value = call_user_func(array($entity, "get" . ucfirst($field)));

                //$field = Inflector::tableize($field);

                if ($value instanceof \DateTime)
                {
                    // @todo Fix DATE and TIME format too
                    $data[$field] = $value->format('Y-m-d\TH:i:s');
                }
                else if (is_object($value))
                {
                    $data[$field] = (string)$value;
                }
                else
                {
                    $data[$field] = $value;
                }
            }

            foreach ($metadata->associationMappings as $field => $mapping) {
                $key = $field;

                if ($mapping['isCascadeDetach'])
                {
                    $data[$key] = $metadata->reflFields[$field]->getValue($entity);

                    if (null !== $data[$key])
                    {
                        $data[$key] = $this->_serializeEntity($data[$key]);
                    }
                }
                else if ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE)
                {
                    if (null !== $metadata->reflFields[$field]->getValue($entity))
                    {
                        $data[$key] = $this->_serializeEntity($metadata->reflFields[$field]->getValue($entity));
                    }
                    else
                    {
                        $data[$key] = null;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Serialize an entity to an array
     *
     * @param The entity $entity
     * @return array
     */
    public function toArray($entity)
    {
        return $this->_serializeEntity($entity);
    }


    /**
     * Convert an entity to a JSON object
     *
     * @param The entity $entity
     * @return string
     */
    public function toJson($entity)
    {
        return json_encode($this->toArray($entity));
    }

    /**
     * Convert an entity to XML representation
     *
     * @param The entity $entity
     * @throws Exception
     */
    public function toXml($entity)
    {
        throw new Exception('Not yet implemented');
    }
}