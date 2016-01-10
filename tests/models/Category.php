<?php
/**
 * Category Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Models;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\Join;
use SweetORM\Structure\Annotation\OneToMany;
use SweetORM\Structure\Annotation\Table;
use SweetORM\Structure\Annotation\Column;

/**
 * Class Category
 * @package SweetORM\Tests\Models
 *
 * @\SweetORM\Structure\Annotation\Entity
 * @Table(name="category")
 */
class Category extends Entity
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
     * @var string
     * @Column(type="string")
     */
    public $description;



    // One To Many relationship
    /**
     * @var Post[]
     * @OneToMany(targetEntity="SweetORM\Tests\Models\Post")
     * @Join(column="id", targetColumn="categoryid")
     */
    public $posts;
}