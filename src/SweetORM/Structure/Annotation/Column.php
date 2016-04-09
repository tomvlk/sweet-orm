<?php
/**
 * Column Annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Annotation;


use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Enum;
use SweetORM\Structure\BaseAnnotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Column implements BaseAnnotation
{
    /**
     * @Required
     * @Enum({"string", "integer", "float", "double", "text", "date", "bool"})
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


    /**
     * READ ONLY!
     *
     * Constraint if provided will be set here.
     *
     * @var Constraint|null
     */
    public $constraint;



    /**
     * Parse and return default value of column.
     *
     * @return string|int|null default value
     */
    public function defaultValue()
    {
        if ($this->default === null) {
            return null;
        }

        $default = $this->default;

        if (strstr($this->default, '{{') && strstr($this->default, '}}')) {
            // Parse special replacements (if we have one)
            $default = str_replace("{{CURRENT_TIME}}", date('c', time()), $default);
        }

        return $default;
    }
}
