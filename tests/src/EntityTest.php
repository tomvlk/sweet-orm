<?php
/**
 * Entity Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests;

use SweatORM\ConnectionManager;
use SweatORM\Tests\Models\Category;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Prepare by injecting configuration
        ConnectionManager::clearConnection();
        Utilities::injectDatabaseConfiguration();
    }

    /**
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     */
    public function testFindQueryBuilder()
    {
        Utilities::resetDatabase();

        // Find All
        $all = Category::find()->all();
        $this->assertEquals(4, count($all));
        foreach($all as $single) {
            $this->assertInstanceOf(Category::class, $single);
        }


    }


}
