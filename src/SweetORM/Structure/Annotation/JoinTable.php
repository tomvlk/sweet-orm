<?php
/**
 * Join with a join table in a Relation
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
 * JoinTable declaration
 *
 * @package SweetORM\Structure
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class JoinTable implements BaseAnnotation
{
    /**
     * Join Table Name
     * @var string
     * @Required()
     */
    public $name;

    /**
     * Fill in the join column for the current local entity column.
     * @var JoinColumn
     */
    public $column;


    /**
     * Fill in the join column for the target entity column
     * @var JoinColumn
     */
    public $targetColumn;


    /**
     * Dont fill this in!
     * @var string
     */
    public $sourceEntityName;

    /**
     * Dont fill this in!
     * @var string
     */
    public $targetEntityName;
}