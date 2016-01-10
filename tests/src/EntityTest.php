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
use SweatORM\Exception\RelationException;
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
        $posts = Post::find()->where(array('categoryid' => '1'))->sort("title", "ASC")->limit(2)->offset(2)->all();
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
        $post->category = Category::get(1);

        // Will not have all required columns filled for now, need to give an exception!
        try {
            $post->save();
            $this->assertTrue(false);
        } catch (QueryException $qe) {
            $this->assertTrue(true);
        }

        // Now fill in the correct fields
        $post->authorid = 1;
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
        $post->categoryid = 1;
        $post->authorid = 1;
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

    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Structure\RelationManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     */
    public function testDeleting()
    {
        Utilities::resetDatabase();

        // Create test entity
        $post = new Post();
        $post->categoryid = 1;
        $post->authorid = 1;
        $post->title = "Sample_Update_1";
        $post->content = "Sample Insert 1";

        $status = $post->save();
        $this->assertTrue($status);


        $id = $post->id;
        // Delete it

        $status = $post->delete();
        $this->assertTrue($status);

        // Check if it exists
        $post = Post::get($id);
        $this->assertFalse($post);

        // Trying to delete a non saved entity
        $post = new Post();
        $status = $post->delete();

        $this->assertFalse($status);
    }




    // Relation
    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Structure\RelationManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     * @covers \SweatORM\Database\Solver
     * @covers \SweatORM\Database\Solver\OneToOne
     */
    public function testOneToOneRelation()
    {
        Utilities::resetDatabase();

        $post = new Post();
        $post->categoryid = 1;
        $post->authorid = 1;
        $post->title = "Sample_Relation_1";
        $post->content = "Sample 1";

        $status = $post->save();
        $this->assertTrue($status);

        // Get invalid property
        try {
            $post->nononon;
            $this->assertTrue(false);
        } catch (RelationException $re) {
            $this->assertTrue(true);
        }

        // Get property 'category'.
        $category = $post->category;
        $this->assertInstanceOf(Category::class, $category);

        // Lazy loading should cache it. We can see it working when checking the coverage reports.
        $category = $post->category;
        $this->assertInstanceOf(Category::class, $category);
    }

    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Structure\RelationManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     * @covers \SweatORM\Database\Solver
     * @covers \SweatORM\Database\Solver\OneToMany
     */
    public function testOneToManyRelation()
    {
        Utilities::resetDatabase();

        // Get category 1
        /** @var Category $cat */
        $cat = Category::get(1);


        // Get posts
        $posts = $cat->posts;
        $this->assertEquals(4, count($posts));

        // The lazy category of the post should be exactly the same as the one we had fetched before!
        foreach($posts as $post) {
            $this->assertEquals($cat, $post->category);
        }

        // Get posts again (testing cache)
        $posts = $cat->posts;

        $this->assertEquals(4, count($posts));
    }


    /**
     * @covers \SweatORM\Entity
     * @covers \SweatORM\EntityManager
     * @covers \SweatORM\Structure\RelationManager
     * @covers \SweatORM\Database\Query
     * @covers \SweatORM\Database\QueryGenerator
     * @covers \SweatORM\Database\Solver
     * @covers \SweatORM\Database\Solver\OneToOne
     */
    public function testSaveOneToOne()
    {
        Utilities::resetDatabase();

        $category1 = Category::get(1);

        // Make new post in category 1
        $post = new Post();
        $post->authorid = 1;
        $post->category = $category1;
        $post->title = "Sample_Relation_Save";
        $post->content = "Sample";

        $status = $post->save();
        $this->assertTrue($status);
        $this->assertEquals(1, $post->categoryid);


        // Test wrong saves
        $category = new Category();

        // Unsaved category
        $post = new Post();

        try{
            $post->category = $category;
            $this->assertTrue(false);
        }catch(RelationException $re) {
            $this->assertTrue(true);
        }

        try{ // Wrong type and not null
            $post->category = false;
            $this->assertTrue(false);
        }catch(RelationException $re) {
            $this->assertTrue(true);
        }

        try{ // Wrong property
            $post->asasdf = false;
            $this->assertTrue(false);
        }catch(RelationException $re) {
            $this->assertTrue(true);
        }

        // Try to set it null, should give no errors, only when saving!
        $post->category = null;
    }
}
