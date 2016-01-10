<?php
/**
 * Entity Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests;

use SweatORM\ConnectionManager;
use SweatORM\Exception\QueryException;
use SweatORM\Tests\Models\Category;
use SweatORM\Tests\Models\Post;

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


        // Try to find with wrong column
        try {
            Category::find()->where('nonexisting', 1)->all();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }


        // This MUST throw exceptions:
        try { // Invalid limit type
            Category::find()->limit("test")->one();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid offset type
            Category::find()->offset("test")->one();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid sort column
            Category::find()->where('id', '<>', null)->where('id', '<>', false)->sort(null)->all();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid order by type
            Category::find()->where('id', '<>', null)->where('id', '<>', false)->sort('id', 'falsfasdfasdf')->all();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid Operator
            Category::find()->where('id', 'SADSDASD', 1)->all();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid Mode
            Category::find()->insert()->into()->where('id', 'SADSDASD', 1)->all();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid Mode
            Category::find()->insert()->into()->sort('id')->apply();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid Mode
            Category::find()->insert()->into()->limit(1)->apply();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid Mode
            Category::find()->insert()->into()->offset(1)->apply();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        try { // Invalid Mode
            Category::find()->insert()->into()->one();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        // END FAIL TESTS


        // Weird but good!
        $all = Category::find()->where('id', 'IS NOT', null)->where('id', '!=', false)->all();
        $this->assertEquals(4, count($all));

        // Sorting
        $all = Category::find()->sort('description')->all();
        $this->assertEquals(4, count($all));

        // Where IN
        $all = Category::find()->where('id', 'IN', array(1,2))->all();
        $this->assertEquals(2, count($all));


        // Test with valid where, limit and offset in one
        $posts = Post::find()->where(array('category' => '1'))->sort("title", "ASC")->limit(2)->offset(2)->all();
        $this->assertEquals(2, count($posts));
        $this->assertEquals("Sample News #3", $posts[0]->title);
        $this->assertEquals("Sample News #4", $posts[1]->title);
    }

    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     */
    public function testGet()
    {
        Utilities::resetDatabase();

        $cat1 = Category::get(1);
        $cat2 = Category::get(2);
        $catnone = Category::get(999999);

        $this->assertInstanceOf(Category::class, $cat1);
        $this->assertInstanceOf(Category::class, $cat2);
        $this->assertFalse($catnone);
    }



    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     */
    public function testInserting()
    {
        Utilities::resetDatabase();

        // Make new post in category 1
        $post = new Post();
        $post->category = 1;

        // Will not have all required columns filled for now, need to give an exception!
        try {
            $post->save();
            $this->assertTrue(false);
        } catch (QueryException $qe) {
            $this->assertTrue(true);
        }


        // Now fill in the correct fields
        $post->author = 1;
        $post->title = "Sample_Insert_1";
        $post->content = "Sample Insert Content";

        // Try again, must succeed
        $result = $post->save();

        $this->assertTrue($result);
        $this->assertEquals(11, $post->_id);
        $this->assertEquals(11, $post->id);
    }


    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     */
    public function testUpdating()
    {
        Utilities::resetDatabase();

        $post = new Post();
        $post->category = 1;
        $post->author = 1;
        $post->title = "Sample_Update_1";
        $post->content = "Sample Insert 1";

        $status = $post->save();
        $this->assertTrue($status);

        // Update the post, change the content
        $post->content = "Sample Update 1";

        $status = $post->save();
        $this->assertTrue($status);

        // Get the post back in another find query
        $updated = Post::get($post->_id);
        $this->assertEquals("Sample Update 1", $updated->content);
    }


}
