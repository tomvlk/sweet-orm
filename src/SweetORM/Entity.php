<?php
/**
 * Entity Abstract Class
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM;
use SweetORM\Database\Query;
use SweetORM\Exception\RelationException;

/**
 * Entity. Extend this class for all the functionality you get with the ORM
 *
 * @package SweetORM
 */
abstract class Entity
{
    /**
     * @var boolean Saved state
     */
    public $_saved = false;

    /**
     * @var int|string Primary Key value
     */
    public $_id = null;


    /**
     * Entity constructor.
     */
    public final function __construct()
    {
        EntityManager::getInstance()->afterConstruct($this);
    }

    /**
     * Relationship catcher
     * @param $name
     * @return int|string|mixed
     */
    public final function __get($name)
    {
        if ($name === '_id') {
            return EntityManager::getInstance()->getId($this);
        }
        return EntityManager::getInstance()->getLazy($this, $name);
    }

    /**
     * Set relationship entity
     *
     * @param $name
     * @param $value
     * @throws \Exception|RelationException
     */
    public final function __set($name, $value)
    {
        EntityManager::getInstance()->setLazy($this, $name, $value);
    }


    /** ==== Entity Instance Operations **/

    /**
     * Save Entity
     * @return bool
     */
    public function save()
    {
        return EntityManager::getInstance()->save($this);
    }

    /**
     * Delete Entity
     * @return bool
     */
    public function delete()
    {
        return EntityManager::getInstance()->delete($this);
    }


    /** ==== Entity Static Operation Functions, will apply on specific entities ==== **/


    /**
     * Start Query for finding specific Entities.
     * @return Query
     */
    public static function find()
    {
        return EntityManager::getInstance()->find(static::class);
    }

    /**
     * Start query building
     * @return Query
     */
    public static function query()
    {
        return EntityManager::getInstance()->query(static::class);
    }

    /**
     * Get Entity with Primary Key value
     *
     * @param int|string $primaryValue
     * @return Entity|false|object
     */
    public static function get($primaryValue)
    {
        return EntityManager::getInstance()->get(static::class, $primaryValue);
    }
}
