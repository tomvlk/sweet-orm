<?php
/**
 * Post Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests\Models;

use SweatORM\Entity;
use SweatORM\Structure\Column;
use SweatORM\Structure\Table;

/**
 * Class Post
 * @package SweatORM\Tests\Models
 *
 * @\SweatORM\Structure\Entity()
 * @Table(name="post")
 */
class Post extends Entity
{
    /**
     * @var int
     * @Column
     */
    public $id;

    /**
     * @var int
     * @Column
     */
    public $author;

    /**
     * @var int
     * @Column
     */
    public $category;

    /**
     * @var string
     * @Column
     */
    public $title;

    /**
     * @var string
     * @Column
     */
    public $content;
}