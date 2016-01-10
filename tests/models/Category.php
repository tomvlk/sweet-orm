<?php
/**
 * Category Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests\Models;

use SweatORM\Entity;
use SweatORM\Structure\OneToMany;
use SweatORM\Structure\Table;
use SweatORM\Structure\Column;

/**
 * Class Category
 * @package SweatORM\Tests\Models
 *
 * @\SweatORM\Structure\Entity
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


    // Relations to other Entities

    /**
     * @var Post[]
     * @OneToMany(targetEntity="SweatORM\Tests\Models\Post", )
     */
    public $posts;
}