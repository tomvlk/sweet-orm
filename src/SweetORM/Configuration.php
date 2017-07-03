<?php
/**
 * Configuration Holder
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Configuration Holder
 * Pleaase use this class to set your database configuration that will be used by the ORM
 *
 * @package SweetORM
 */
class Configuration
{
    /**
     * Contains the database connection configuration and the ORM specific configuration.
     *
     * @var array
     */
    private static $config = array();


    /**
     * Are the annotations registered?
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Set configuration value.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$config[$key] = $value;

        // Check if we need to register the Annotations
        if (!self::$registered) {
            AnnotationRegistry::registerFile(__DIR__ . '/Structure/SweetAnnotations.php');
            self::$registered = true;
        }
    }

    /**
     * Register annotations when not yet done.
     *
     * @codeCoverageIgnore
     */
    public static function registerAnnotations ()
    {
        // Check if we need to register the Annotations
        if (!self::$registered) {
            AnnotationRegistry::registerFile(__DIR__ . '/Structure/SweetAnnotations.php');
            self::$registered = true;
        }
    }


    /**
     * Get configuration value.
     *
     * @param string $key
     * @return mixed|null value or null
     */
    public static function get($key)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : null;
    }

    /**
     * Add configuration subvalue to a array value already existing.
     *
     * @param string $key
     * @param mixed|array $value One value only.
     * @return bool successful or not
     */
    public static function add($key, $value)
    {
        if (!isset(self::$config[$key]) || !is_array(self::$config[$key])) {
            return false;
        }
        array_push(self::$config[$key], $value);
        return true;
    }
}
