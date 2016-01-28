<?php
/**
 * Entity Manager
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM;

use Doctrine\Common\Collections\ArrayCollection;
use SweetORM\Database\Query;
use SweetORM\Exception\ConstraintViolationException;
use SweetORM\Exception\ORMException;
use SweetORM\Exception\RelationException;
use SweetORM\Structure\Annotation\JoinTable;
use SweetORM\Structure\EntityStructure;
use SweetORM\Structure\Indexer\EntityIndexer;
use SweetORM\Structure\RelationManager;

class EntityManager
{
    /** @var EntityManager */
    private static $instance;

    /** @var EntityStructure[] $entities Structures */
    private $entities = array();

    /** @var JoinTable[] $joinTables Join Tables storage */
    private $joinTables = array();

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
     * After Fetch entity hook
     *
     * @param bool $saved
     * @param Entity[]|Entity|null $result
     * @return Entity|ArrayCollection<Entity>|null
     */
    public function afterFetch($saved, $result)
    {
        // Multiple results
        if (is_array($result)) {
            $all = new ArrayCollection();
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $all[] = self::afterFetch($saved, $entity);
                }
            }
            return $all;
        }

        // Normal single processing.
        if ($result instanceof Entity) {
            $result->_saved = $saved;

            // Process relation properties, delete the 'real' props
            $this->injectVirtualProperties($result);
        }
        return $result;
    }

    /**
     * Did construct entity, mostly when making new one or fetched.
     * @param Entity $entity
     */
    public function afterConstruct(&$entity)
    {
        // First we want to get rid of the virtual relation properties.
        $this->injectVirtualProperties($entity);

        // Get rid of the $_id property, it will be virtual too
        unset($entity->_id);
    }

    /**
     * Inject Virtual Properties for relations
     *
     * @param Entity $entity
     */
    public function injectVirtualProperties($entity)
    {
        $structure = $this->getEntityStructure($entity);
        if (count($structure->relationProperties) > 0) {
            foreach ($structure->relationProperties as $removeProperty) {
                unset($entity->{$removeProperty});
            }
        }
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
        } // @codeCoverageIgnore

        return isset($this->entities[$entityClassName]);
    }

    /**
     * Register a join table. Don't use this directly!
     *
     * @param JoinTable $joinTable
     * @throws RelationException
     */
    public function registerJoinTable($joinTable)
    {
        if (! $joinTable instanceof JoinTable) {
            throw new RelationException("Join Table register failure!"); // @codeCoverageIgnore
        }

        // Check for existing
        if (isset($this->joinTables[$joinTable->name])) {
            // Ignore. We already have it, lets hope the user knows what he is doing!
            return; // @codeCoverageIgnore
        }

        // Add and make sure we don't have duplicates
        $this->joinTables[$joinTable->name] = $joinTable;
    }

    /**
     * Get the join table for the table name.
     *
     * @param string $name Table name to search for.
     * @return null|JoinTable
     *
     * @codeCoverageIgnore Will be ignored for now as it isn't used yet.
     */
    public function getJoinTable($name)
    {
        if (isset($this->joinTables[$name])) {
            return $this->joinTables[$name];
        }
        return null;
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


    /**
     * Get entity ID value
     *
     * @param Entity $entity
     * @return int|string|null
     *
     */
    public function getId(&$entity)
    {
        $structure = $this->getEntityStructure($entity);

        if (isset($entity->{$structure->primaryColumn->propertyName})) {
            return $entity->{$structure->primaryColumn->propertyName};
        }
        return null;
    }


    /**
     * Will be called for getting the relationship result, lazy loading.
     *
     * @param Entity $entity
     * @param string $name
     *
     * @return mixed
     * @throws RelationException When not found in relation, or the relation is invalid.
     */
    public function getLazy($entity, $name)
    {
        // Verify if virtual property exists
        if (! in_array($name, $this->getEntityStructure($entity)->relationProperties)) {
            throw new RelationException("Property '".$name."' is not a valid and declared property, or relation property!");
        }

        return RelationManager::with($entity)->fetch($name);
    }

    /**
     * Set a virtual property
     *
     * @param Entity $entity
     * @param string $name
     * @param Entity $value
     * @throws RelationException
     * @throws \Exception
     */
    public function setLazy($entity, $name, $value)
    {
        // Verify if virtual property exists, if not then just don't set it!
        if (! in_array($name, $this->getEntityStructure($entity)->relationProperties)) {
            return;
        }

        // Verify if value is also an entity!
        if (! $value instanceof Entity && $value !== null) {
            throw new RelationException("Property '".$name."' is a reference to a relationship, you should set the entity of that relationship!");
        }

        // Pass to the relationmanager
        RelationManager::with($entity)->set($name, $value);
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
            $result = $query->update()->set($this->getEntityDataArray($entity))->where(array($structure->primaryColumn->name => $entity->_id))->apply();

            // Update relations
            RelationManager::with($entity)->saveRelations();

            return $result;
        } else {
            // Insert
            $id = $query->insert()->into($structure->tableName)->values($this->getEntityDataArray($entity))->apply();

            if ($id === false) {
                return false; // @codeCoverageIgnore
            }

            // Update relations
            RelationManager::with($entity)->saveRelations();

            // Save ID and state
            $entity->{$structure->primaryColumn->propertyName} = $id;
            $entity->_id = $id;
            $entity->_saved = true;

            return true;
        }
    }


    /**
     * Delete entity from database
     * @param Entity $entity
     * @return bool
     */
    public function delete($entity)
    {
        $query = new Query($entity, false);
        $structure = $this->getEntityStructure($entity);

        if ($entity->_saved) {
            return $query->delete($structure->tableName)->where(array($structure->primaryColumn->name => $entity->_id))->apply();
        }
        return false;
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
     * Start a query
     *
     * @param $entity
     * @param bool $verify
     *
     * @return Query
     */
    public static function query($entity, $verify = true)
    {
        return new Query($entity, false, $verify);
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
     *
     * @throws ConstraintViolationException
     */
    private function getEntityDataArray($entity)
    {
        $data = array();

        $structure = $this->getEntityStructure($entity);
        $columns = $structure->columns;

        foreach ($columns as $column) {
            if (isset($entity->{$column->propertyName}) && $entity->{$column->propertyName} !== null) {
                $data[$column->name] = $entity->{$column->propertyName};
            } elseif (isset($entity->{$column->propertyName}) && $entity->{$column->propertyName} === null) {
                // Throw exception if null not allowed.
                if (! $column->null && $column->default === null) {
                    throw new ConstraintViolationException("Column '".$column->propertyName."' can't be empty! Null not allowed!");
                }

                // Fill in default value if allowed
                if (! $column->null && $column->default !== null) {
                    $data[$column->name] = $column->defaultValue();
                } else {
                    $data[$column->name] = null;
                }
            } else {
                $data[$column->name] = null;
            }
        }
        return $data;
    }
}