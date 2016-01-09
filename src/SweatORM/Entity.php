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


}
