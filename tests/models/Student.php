<?php
/**
 * Student Entity (for tests)
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Models;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\Column;
use SweetORM\Structure\Annotation\EntityClass;
use SweetORM\Structure\Annotation\JoinColumn;
use SweetORM\Structure\Annotation\JoinTable;
use SweetORM\Structure\Annotation\ManyToMany;
use SweetORM\Structure\Annotation\Table;

/**
 * @EntityClass()
 * @Table(name="student")
 */
class Student extends Entity
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
    public $name;


    /**
     * @var Course[]
     * @ManyToMany(targetEntity="SweetORM\Tests\Models\Course")
     * @JoinTable(name="student_courses",
     *     column=       @JoinColumn(name="student_id", entityColumn="id"),
     *     targetColumn= @JoinColumn(name="course_id", entityColumn="id")
     * )
     */
    public $courses;
}