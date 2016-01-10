<?php
/**
 * Table Annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Annotation;


use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Required;
use SweatORM\Structure\BaseAnnotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Table implements BaseAnnotation
{
    /**
     * @Required
     * @var string
     */
    public $name;




}