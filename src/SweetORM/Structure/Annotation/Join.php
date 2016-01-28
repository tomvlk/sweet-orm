<?php
/**
 * Join in a Relation
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
 * Join declaration
 *
 * @package SweetORM\Structure
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Join implements BaseAnnotation
{
    /**
     * Local Column
     * @var string
     * @Required()
     */
    public $column;

    /**
     * Target Column at the target side
     * @var string
     * @Required()
     */
    public $targetColumn;

    /**
     * Optional join? Will not fail but stay NULL when $column is not set at the source.
     * @var bool
     */
    public $optional = true;
}