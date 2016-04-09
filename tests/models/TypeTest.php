<?php
/**
 * TypeTest Entity.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Models;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\EntityClass;
use SweetORM\Structure\Annotation\Join;
use SweetORM\Structure\Annotation\OneToMany;
use SweetORM\Structure\Annotation\Table;
use SweetORM\Structure\Annotation\Column;

/**
 * Class TypeTest
 * @package SweetORM\Tests\Models
 *
 * @EntityClass()
 * @Table(name="typetest")
 */
class TypeTest extends Entity
{
    /**
     * @var int
     * @Column(type="integer", primary=true, autoIncrement=true)
     */
    public $id;

    /**
     * @var string
     * @Column(type="string")
     */
    public $string;

    /**
     * @var string
     * @Column(type="text")
     */
    public $text;

    /**
     * @var double
     * @Column(type="double")
     */
    public $double;

    /**
     * @var float
     * @Column(type="float")
     */
    public $float;

    /**
     * @var boolean
     * @Column(type="bool")
     */
    public $boolean;
}
