<?php
/**
 * Entity Abstract Class
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM;
use SweatORM\Exception\RelationException;

/**
 * Entity. Extend this class for all the functionality you get with the ORM
 *
 * @package SweatORM
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
        EntityManager::getInstance()->injectVirtualProperties($this);
    }

    /**
     * Relationship catcher
     * @param $name
     * @return mixed
     */
    public final function __get($name)
    {
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


    /** ==== Entity Instance Operatios **/

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
     */
    public static function find()
    {
        return EntityManager::getInstance()->find(static::class);
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
