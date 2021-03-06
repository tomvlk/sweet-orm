<?php
/**
 * ManyToOne Relation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Annotation;

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * Many To One relation, mostly parent
 *
 * @package SweetORM\Structure
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class ManyToOne extends Relation
{
}