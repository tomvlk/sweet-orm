<?php
/**
 * ConstraintTest Entity.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Models;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\Constraint;
use SweetORM\Structure\Annotation\EntityClass;
use SweetORM\Structure\Annotation\Join;
use SweetORM\Structure\Annotation\OneToMany;
use SweetORM\Structure\Annotation\Table;
use SweetORM\Structure\Annotation\Column;

/**
 * Class ConstraintTest
 * @package SweetORM\Tests\Models
 *
 * @EntityClass()
 * @Table(name="constrainttest")
 */
class ConstraintTest extends Entity
{
    /**
     * @var int
     * @Column(type="integer", primary=true, autoIncrement=true)
     */
    public $id;

    /**
     * @var string
     * @Column(type="string", null=true)
     * @Constraint(
     *     valid="url"
     * )
     */
    public $url;

    /**
     * @var string
     * @Column(type="string", null=true)
     * @Constraint(
     *     startsWith="www.",
     *     minLength=20
     * )
     */
    public $startsWith;

    /**
     * @var string
     * @Column(type="text", null=true)
     * @Constraint(
     *     endsWith=".com",
     *     maxLength=20
     * )
     */
    public $endsWith;

    /**
     * @var float
     * @Column(type="float", null=true)
     * @Constraint(
     *     minValue=20
     * )
     */
    public $minValue;

    /**
     * @var double
     * @Column(type="double", null=true)
     * @Constraint(
     *     minValue=44.5,
     *     maxValue=55.5
     * )
     */
    public $between;

    /**
     * @var string
     * @Column(type="string", null=true)
     * @Constraint(
     *     enum={"yes", "no"}
     * )
     */
    public $question;

    /**
     * @var string
     * @Column(type="string", null=true)
     * @Constraint(
     *     regex="/(?<=\d\/|\.be\/|v[=\/])([\w\-]{11,})|^([\w\-]{11})$/im"
     * )
     * Check youtube id (link)
     */
    public $youtube;
}
