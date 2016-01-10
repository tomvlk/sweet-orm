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
     * @param string|Entity $entityClassName
     */
    public function registerEntity($entityClassName)
    {
        $indexer = new EntityIndexer($entityClassName);
        $structure = $indexer->getEntity();

        $this->entities[$structure->name] = $structure;
    }

    /**
     * Is the entity already registered?
     * @param string|Entity $entityClassName
     * @return bool
     */
    public function isRegistered($entityClassName)
    {
        if ($entityClassName instanceof Entity) {
            $reflection = new \ReflectionClass($entityClassName); // @codeCoverageIgnore
            $entityClassName = $reflection->getName(); // @codeCoverageIgnore
        }

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
     * @param string|Entity $entityClassName
     * @return EntityStructure|false
     */
    public function getEntityStructure($entityClassName)
    {
        if ($entityClassName instanceof Entity) {
            $reflection = new \ReflectionClass($entityClassName);
            $entityClassName = $reflection->getName();
        }

        if (! $this->isRegistered($entityClassName)) {
            $this->registerEntity($entityClassName);
        }
        return $this->entities[$entityClassName];
    }



    /** ==== Entity Instance Operations **/

    /**
     * Save Entity (will insert or update)
     *
     * @param Entity $entity
     *
     * @return bool status of save
     */
    public function save($entity)
    {
        $query = new Query($entity, false);
        $structure = $this->getEntityStructure($entity);

        if ($entity->_saved) {
            // Update
            return $query->update()->set($this->getEntityDataArray($entity))->where(array($structure->primaryColumn->name => $entity->_id))->apply();
        } else {
            // Insert
            $id = $query->insert()->into($structure->tableName)->values($this->getEntityDataArray($entity))->apply();

            if ($id === false) {
                return false; // @codeCoverageIgnore
            }

            // Save ID and state
            $entity->{$structure->primaryColumn->propertyName} = $id;
            $entity->_id = $id;
            $entity->_saved = true;

            return true;
        }
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

    /** ====== **/

    /**
     * Get entity column=>value array.
     * @param Entity $entity
     * @return array
     */
    private function getEntityDataArray($entity)
    {
        $data = array();

        $structure = $this->getEntityStructure($entity);
        $columns = $structure->columns;

        foreach ($columns as $column) {
            if (isset($entity->{$column->propertyName})) {
                $data[$column->name] = $entity->{$column->propertyName};
            } else {
                $data[$column->name] = null;
            }
        }
        return $data;
    }
}