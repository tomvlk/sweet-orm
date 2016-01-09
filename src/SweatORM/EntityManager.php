<?php
/**
 * Entity Manager
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM;


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
     * Get entity structure class for using metadata
     *
     * @param string $entityClassName
     * @return EntityStructure|false
     */
    public function getEntityStructure($entityClassName)
    {
        if ($this->isRegistered($entityClassName)){
            return $this->entities[$entityClassName];
        }
        return false;
    }
}