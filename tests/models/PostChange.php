<?php
/**
 * Post Change (join entity). Entity for tests
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
 * Class PostChange
 * @package SweetORM\Tests\Models
 *
 * @EntityClass()
 * @Table(name="postchange")
 */
class PostChange extends Entity
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
    public $postid;

    /**
     * @var Post
     * @ManyToOne(targetEntity="SweetORM\Tests\Models\Post")
     * @Join(column="postid", targetColumn="id")
     */
    public $post;

    /**
     * @var int
     * @Column(type="integer")
     */
    public $authorid;

    /**
     * @var Author
     * @ManyToOne(targetEntity="SweetORM\Tests\Models\Author")
     * @Join(column="authorid", targetColumn="id")
     */
    public $author;



    /**
     * @var string
     * @Column(type="date", default="{{CURRENT_TIME}}")
     */
    public $created;
}