<?php
/**
 * Constraint Annotation, used for validating.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Annotation;

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Enum;
use SweetORM\Structure\BaseAnnotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Constraint implements BaseAnnotation
{
    /**
     * Minimum characters.
     * @var int
     */
    public $minLength;

    /**
     * Maximum characters.
     * @var int
     */
    public $maxLength;

    /**
     * @var string
     * @Enum({"email","url"})
     */
    public $valid;

    /**
     * Must be one of the options provided.
     * @var array
     */
    public $enum;

    /**
     * Starts with.
     * @var string
     */
    public $startsWith;

    /**
     * Ends with
     * @var string
     */
    public $endsWith;
}