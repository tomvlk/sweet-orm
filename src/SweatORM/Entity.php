<?php
/**
 * Entity Abstract Class
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM;

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
    private $_id;







    /** ==== Entity Instance Operatios **/
    public function save()
    {
        return EntityManager::getInstance()->save($this);
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
     * @return Entity|false
     */
    public static function get($primaryValue)
    {
        return EntityManager::getInstance()->get(static::class, $primaryValue);
    }
}
