<?php
/**
 * EntityTest Test the entity annotation
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Structure;

use PHPUnit\Framework\TestCase;
use SweetORM\EntityManager;
use SweetORM\Structure\Indexer\EntityIndexer;
use \SweetORM\Tests\Models\Post;
use \SweetORM\Tests\Models\Category;

/**
 * Class EntityTest
 * @package SweetORM\Tests\Structure
 *
 * @coversDefaultClass \SweetORM\Structure\Indexer\EntityIndexer
 */
class EntityTest extends TestCase
{

    /**
     * @covers \SweetORM\Structure\Indexer\EntityIndexer
     * @covers \SweetORM\Structure\Indexer\TableIndexer
     * @covers \SweetORM\Structure\Indexer\ColumnIndexer
     * @covers \SweetORM\Structure\Indexer\RelationIndexer
     * @covers \SweetORM\Structure\EntityStructure
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

    /**
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\EntityManager::getInstance
     * @covers \SweetORM\EntityManager::isRegistered
     * @covers \SweetORM\EntityManager::registerEntity
     * @covers \SweetORM\EntityManager::getEntityStructure
     * @covers \SweetORM\Structure\EntityStructure
     * @covers \SweetORM\Structure\Indexer\TableIndexer
     * @covers \SweetORM\Structure\Indexer\EntityIndexer
     * @covers \SweetORM\Structure\Indexer\ColumnIndexer
     * @covers \SweetORM\Structure\Indexer\RelationIndexer
     */
    public function testRegisterEntity()
    {
        $manager = EntityManager::getInstance();
        $manager->clearRegisteredEntities();

        $registered = $manager->isRegistered(Post::class);
        $this->assertFalse($registered);



        $manager->registerEntity(Category::class);

        $registered = $manager->isRegistered(Category::class);
        $this->assertTrue($registered);

        $structure = $manager->getEntityStructure(Category::class);
        $this->assertInstanceOf("\\SweetORM\\Structure\\EntityStructure", $structure);
        $this->assertEquals(Category::class, $structure->name);
        $this->assertEquals("category", $structure->tableName);
    }


}
