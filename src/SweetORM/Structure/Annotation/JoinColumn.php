<?php
/**
 * Join Column, used in Join Tables
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
 * JoinColumn declaration. Use this annotation inside of the JoinTable annotation.
 *
 * @package SweetORM\Structure
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class JoinColumn implements BaseAnnotation
{
    /**
     * The name of the column used in the join table.
     * @var string
     * @Required()
     */
    public $name;

    /**
     * The name of the column used in the entity. This should be the entity side of the relationship (either sides).
     * @var string
     * @Required()
     */
    public $entityColumn;
}