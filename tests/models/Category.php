<?php
/**
 * Category Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests\Models;

use SweatORM\Entity;
use SweatORM\Structure\Annotation\Join;
use SweatORM\Structure\Annotation\OneToMany;
use SweatORM\Structure\Annotation\Table;
use SweatORM\Structure\Annotation\Column;

/**
 * Class Category
 * @package SweatORM\Tests\Models
 *
 * @\SweatORM\Structure\Annotation\Entity
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
     * @OneToMany(targetEntity="SweatORM\Tests\Models\Post")
     * @Join(column="id", targetColumn="categoryid")
     */
    public $posts;
}