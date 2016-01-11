<?php
/**
 * Post Entity for tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Models;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\Column;
use SweetORM\Structure\Annotation\EntityClass;
use SweetORM\Structure\Annotation\Join;
use SweetORM\Structure\Annotation\ManyToOne;
use SweetORM\Structure\Annotation\OneToMany;
use SweetORM\Structure\Annotation\OneToOne;
use SweetORM\Structure\Annotation\Table;

/**
 * Class Post
 * @package SweetORM\Tests\Models
 *
 * @EntityClass()
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
     * @OneToOne(targetEntity="SweetORM\Tests\Models\Category")
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