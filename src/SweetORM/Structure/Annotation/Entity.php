<?php
/**
 * Entity Annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Annotation;

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;
use SweetORM\Structure\BaseAnnotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Entity implements BaseAnnotation
{
}