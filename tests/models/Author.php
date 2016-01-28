<?php
/**
 * Author Entity (for tests)
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Models;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\Column;
use SweetORM\Structure\Annotation\EntityClass;
use SweetORM\Structure\Annotation\Table;

/**
 * Author Entity
 *
 * @EntityClass()
 * @Table(name="author")
 */
class Author extends Entity
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
     * @var string|null
     * @Column(type="string", null=true)
     */
    public $email;
}
