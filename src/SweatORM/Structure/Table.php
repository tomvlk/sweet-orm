<?php
/**
 * Table Annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Table extends Annotation
{
    /**
     * @Annotation\Required()
     * @var string
     */
    public $name;




}