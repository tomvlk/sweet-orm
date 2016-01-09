<?php
/**
 * EntityTest Test the entity annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests\Structure;

use SweatORM\Structure\Indexer\EntityIndexer;
use \SweatORM\Tests\Models\Post;
use \SweatORM\Tests\Models\Category;

/**
 * Class EntityTest
 * @package SweatORM\Tests\Structure
 *
 * @coversDefaultClass \SweatORM\Structure\Indexer\EntityIndexer
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers \SweatORM\Structure\Indexer\EntityIndexer
     * @covers \SweatORM\Structure\Indexer\Indexer
     * @covers \SweatORM\Structure\Indexer\TableIndexer
     * @covers \SweatORM\Structure\Indexer\ColumnIndexer
     * @covers \SweatORM\Structure\EntityStructure
     */
    public function testEntityAnnotation()
    {
        // Test indexer
        $indexer = new EntityIndexer(Post::class);
        $structure = $indexer->getEntity();

        $this->assertEquals("post", $structure->tableName);

        // Category
        $indexer = new EntityIndexer(Category::class);
        $structure = $indexer->getEntity();

        $this->assertEquals("category", $structure->tableName);
    }
}
