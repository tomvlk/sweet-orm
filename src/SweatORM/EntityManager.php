<?php
/**
 * Entity Manager
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM;


use SweatORM\Database\Query;
use SweatORM\Structure\EntityStructure;
use SweatORM\Structure\Indexer\EntityIndexer;

class EntityManager
{
    /** @var EntityManager */
    private static $instance;

    /** @var EntityStructure[] Structures */
    private $entities = array();

    /**
     * Get entity manager
     * @return EntityManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new EntityManager();
        }
        return self::$instance;
    }

    /**
     * Register and index Entity class
     * @param $entityClassName
     */
    public function registerEntity($entityClassName)
    {
        $indexer = new EntityIndexer($entityClassName);
        $structure = $indexer->getEntity();

        $this->entities[$structure->name] = $structure;
    }

    /**
     * Is the entity already registered?
     * @param string $entityClassName
     * @return bool
     */
    public function isRegistered($entityClassName)
    {
        return isset($this->entities[$entityClassName]);
    }

    /**
     * Will clear all registered entities!
     */
    public function clearRegisteredEntities()
    {
        $this->entities = array();
    }


    /**
     * Get entity structure class for using metadata
     *
     * @param string $entityClassName
     * @return EntityStructure|false
     */
    public function getEntityStructure($entityClassName)
    {
        if (! $this->isRegistered($entityClassName)) {
            $this->registerEntity($entityClassName);
        }
        return $this->entities[$entityClassName];
    }





    /** ==== Entity Operation Functions, will apply on specific entities ==== **/


    /**
     * Start a query
     *
     * @param $entity
     * @return Query
     */
    public static function find($entity)
    {
        return new Query($entity);
    }

    /**
     * Get Entity with Primary Key value
     *
     * @param string $entity
     * @param int|string $primaryValue
     * @return false|Entity
     * @throws \Exception
     */
    public static function get($entity, $primaryValue)
    {
        $query = new Query($entity);
        $column = self::getInstance()->getEntityStructure($entity)->primaryColumn;
        $query->where($column->name, $primaryValue);
        return $query->one();
    }
}