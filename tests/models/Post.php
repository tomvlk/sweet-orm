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
     * @Column(type="integer", primary=true)
     */
    public $id;

    /**
     * @var int
     * @Column(type="integer")
     */
    public $author;

    /**
     * @var int
     * @Column(type="integer")
     */
    public $category;

    /**
     * @var string
     * @Column(type="string")
     */
    public $title;

    /**
     * @var string
     * @Column(type="string")
     */
    public $content;
}