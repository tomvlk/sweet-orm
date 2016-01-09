<?php
/**
 * Entity Annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure;


use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Entity implements Annotation
{
    private static $instances = array();

    /**
     * @param $className
     * @codeCoverageIgnore
     */
    public static function getEntity($className)
    {
        $reflection = new \ReflectionClass($className);

        if (! $reflection->isSubclassOf("\\SweatORM\\Entity")) {
            throw new \UnexpectedValueException("The className for getTable should be a class that is extending the SweatORM Entity class");
        }

        if (! isset(self::$instances[$className])) {
            // We need to index it first, lets do it now..

        };
    }
}