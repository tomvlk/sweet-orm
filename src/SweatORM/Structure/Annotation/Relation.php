<?php
/**
 * Relation Abstract Annotation Class
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Annotation;

use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Required;
use SweatORM\Structure\BaseAnnotation;

/**
 * Class Relation
 * @package SweatORM\Structure
 *
 *
 */
abstract class Relation implements BaseAnnotation
{
    /**
     * Full Entity class notation for the target Entity of the relationship.
     * @var string
     */
    public $targetEntity;

    /**
     * Don't change this, it's not supported to not have lazy loaders in your entity relations yet!
     *
     * @Enum({"LAXY"})
     * @var string
     */
    public $fetch = 'LAZY';

    /**
     * Read Only!
     * Join on Relation
     * @var Join
     */
    public $join;

    /**
     *
     * @var bool
     */
    public $source = false;
}