<?php
/**
 * Join in a Relation
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
 * Join declaration
 *
 * @package SweatORM\Structure
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
}