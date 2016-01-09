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


    /**
     * Entity constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        self::indexEntity();
    }


    /**
     * Will be called each time a static call is made, to check if the entity is indexed.
     *
     * @param string $method
     * @param array $parameters
     *
     * @codeCoverageIgnore
     */
    public static function __callStatic($method, $parameters){
        if (method_exists(__CLASS__, $method)) {
            self::indexEntity();
            forward_static_call_array(array(__CLASS__,$method),$parameters);
        }
    }

    /**
     * Index the entity into the Manager
     * @codeCoverageIgnore
     */
    private static function indexEntity()
    {
        if (!EntityManager::getInstance()->isRegistered(static::class)) {
            EntityManager::getInstance()->registerEntity(static::class);
        }
    }
}
