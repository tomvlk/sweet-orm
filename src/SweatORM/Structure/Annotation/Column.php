<?php
/**
 * Column Annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Annotation;


use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Enum;
use SweatORM\Structure\BaseAnnotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Column implements BaseAnnotation
{
    /**
     * @Required
     * @Enum({"string", "integer", "float", "double", "text"})
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $default;

    /**
     * @var bool
     */
    public $primary = false;

    /**
     * @var bool
     */
    public $null = false;

    /**
     * @var bool
     */
    public $autoIncrement = false;


    /**
     * READ ONLY!
     *
     * Property Field in the Entity declaration
     *
     * @var string
     */
    public $propertyName;
}