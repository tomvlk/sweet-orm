<?php
/**
 * Category Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests\Models;

use SweatORM\Entity;
use SweatORM\Structure\Table;
use SweatORM\Structure\Column;

/**
 * Class Category
 * @package SweatORM\Tests\Models
 *
 * @\SweatORM\Structure\Entity()
 * @Table(name="category")
 */
class Category extends Entity
{
    /**
     * @var int
     * @Column
     */
    public $id;

    /**
     * @var string
     * @Column
     */
    public $name;

    /**
     * @var string
     * @Column
     */
    public $description;
}