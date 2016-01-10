<?php
/**
 * Post Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests\Models;

use SweatORM\Entity;
use SweatORM\Structure\Annotation\Column;
use SweatORM\Structure\Annotation\Join;
use SweatORM\Structure\Annotation\ManyToOne;
use SweatORM\Structure\Annotation\OneToMany;
use SweatORM\Structure\Annotation\OneToOne;
use SweatORM\Structure\Annotation\Table;

/**
 * Class Post
 * @package SweatORM\Tests\Models
 *
 * @\SweatORM\Structure\Annotation\Entity
 * @Table(name="post")
 */
class Post extends Entity
{
    /**
     * @var int
     * @Column(type="integer", primary=true, autoIncrement=true)
     */
    public $id;

    /**
     * @var int
     * @Column(type="integer")
     */
    public $authorid;

    /**
     * @var int
     * @Column(type="integer")
     */
    public $categoryid;

    /**
     * @var Category
     * @OneToOne(targetEntity="SweatORM\Tests\Models\Category")
     * @Join(column="categoryid", targetColumn="id")
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