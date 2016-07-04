<?php
/**
 * Entity Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests;

use SweetORM\ConnectionManager;
use SweetORM\Exception\ORMException;
use SweetORM\Exception\QueryException;
use SweetORM\Exception\RelationException;
use SweetORM\Structure\RelationManager;
use SweetORM\Tests\Models\Author;
use SweetORM\Tests\Models\Category;
use SweetORM\Tests\Models\Course;
use SweetORM\Tests\Models\Post;
use SweetORM\Tests\Models\PostChange;
use SweetORM\Tests\Models\Student;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Prepare by injecting configuration
        ConnectionManager::clearConnection();
        Utilities::injectDatabaseConfiguration();
    }



    /**
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     */
    public function testFindQueryBuilder()
    {
        Utilities::resetDatabase();

        // Find All
        $all = Category::find()->all();
        $this->assertCount(4, $all);
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
        try { // Invalid joining types
            Category::find()->join(null, null, null)->one();
            $this->assertTrue(false);
        }catch(QueryException $qe) {
            $this->assertTrue(true);
        }
        // END FAIL TESTS


        // Weird but good!
        $all = Category::find()->where('id', 'IS NOT', null)->where('id', '!=', false)->all();
        $this->assertCount(4, $all);

        // Sorting
        $all = Category::find()->sort('description')->all();
        $this->assertCount(4, $all);

        // Where IN
        $all = Category::find()->where('id', 'IN', array(1,2))->all();
        $this->assertCount(2, $all);

        // Joining
        $all = Category::find()->join('post', 'post.categoryid = category.id')
            ->select('post.*, category.*')
            ->where('post.authorid', '2')
            ->asArray()
            ->all();
        $this->assertCount(3, $all);

        $all = Category::find()->join('post', 'pst.categoryid = category.id', 'JOIN', 'pst')
            ->select('pst.*, category.*')
            ->where('pst.authorid', '2')
            ->asArray()
            ->all();
        $this->assertCount(3, $all);


        // Test with valid where, limit and offset in one
        $posts = Post::find()->where(array('categoryid' => '1'))->sort("title", "ASC")->limit(2)->offset(2)->all();
        $this->assertCount(2, $posts);
        $this->assertEquals("Sample News #3", $posts[0]->title);
        $this->assertEquals("Sample News #4", $posts[1]->title);
    }

    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
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
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
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
        } catch (ORMException $qe) {
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
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
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
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
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
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\OneToOne
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
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\ManyToOne
     */
    public function testManyToOneRelation()
    {
        Utilities::resetDatabase();

        // Get post, and get Author entity from the relation value
        /** @var Post $post */
        $post = Post::get(1);

        /** @var Author $author */
        $author = $post->author;

        $this->assertEquals(1, $author->id);
    }

    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\ManyToOne
     */
    public function testJoinEntity()
    {
        Utilities::resetDatabase();

        // Test fetching POST with ID 1.
        /** @var Post $post */
        $post = Post::get(1);

        // We will get the log of the post
        /** @var PostChange[] $changes */
        $changes = $post->changes;

        $this->assertCount(3, $changes);


        // Add a new change
        $insert = new PostChange();

        $insert->author = $post->author;
        $insert->post = $post;

        // Lets do the insert created time automatically with the default {{CURRENT_TIME}} value.
        $save = $insert->save();

        $this->assertTrue($save);


        // Clear and refetch post with the changes
        Post::clearCache();

        $post = Post::get(1);
        $changes = $post->changes;

        $this->assertCount(4, $changes);
    }


    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\OneToMany
     */
    public function testOneToManyRelation()
    {
        Utilities::resetDatabase();

        // Get category 1
        /** @var Category $cat */
        $cat = Category::get(1);


        // Get posts
        $posts = $cat->posts;
        $this->assertCount(4, $posts);

        // The lazy category of the post should be exactly the same as the one we had fetched before!
        foreach($posts as $post) {
            $this->assertEquals($cat, $post->category);
        }

        // Get posts again (testing cache)
        $posts = $cat->posts;

        $this->assertCount(4, $posts);
    }


    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Structure\Indexer\RelationIndexer::manyToMany
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\ManyToMany
     */
    public function testManyToMany()
    {
        Utilities::resetDatabase();

        // Get student
        /** @var Student $student */
        $student = Student::get(1); // Will have course 1 and 2 in the db.

        $courses = $student->courses;

        $this->assertCount(2, $courses);
        $this->assertEquals(1, $courses[0]->id);
        $this->assertEquals(2, $courses[1]->id);

        // Cache testing
        $courses = $student->courses;

        $this->assertCount(2, $courses);
        $this->assertEquals(1, $courses[0]->id);
        $this->assertEquals(2, $courses[1]->id);



        // Reverse

        /** @var Course $course */
        $course = Course::get(1);

        $students = $course->students;
        $this->assertCount(7, $students);

        // Cache test
        $students = $course->students;
        $this->assertCount(7, $students);
    }



    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\OneToOne
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

        // Try to set it null, should give no errors, only when saving!
        $post->category = null;
    }



    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Structure\RelationManager
     * @covers \SweetORM\Structure\RelationManager::saveRelations
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     * @covers \SweetORM\Database\Solver
     * @covers \SweetORM\Database\Solver\ManyToMany
     * @covers \SweetORM\Database\Solver\ManyToMany::solveSave
     */
    public function testSaveManyToMany()
    {
        Utilities::resetDatabase();

        // We will get student 2 first, the student already got course with id 1!
        $student = Student::get(2); /** @var Student $student */
        $this->assertEquals(2, $student->_id);

        // Add course with id 2
        $student->courses[] = Course::get(2);

        // Check if it's in the array
        $this->assertCount(2, $student->courses);

        // Save the student, this should solve the relation updates too
        $result = $student->save();
        $this->assertTrue($result);

        // Verify if it worked by clearing caches and re-fetch the student and courses
        RelationManager::clearCache();

        $student = Student::get(2);
        $this->assertEquals(2, $student->_id);
        $this->assertCount(2, $student->courses);

        // Check the other way around
        $course = Course::get(2); /** @var Course $course */

        $found = false;
        foreach ($course->students as $student) {
            if ($student->_id == 2) {
                $found = true;
            }
        }
        $this->assertTrue($found);

        // Test deleting all and inserting none
        RelationManager::clearCache();

        $student = Student::get(2);
        $student->courses->clear();

        $student->save();

        RelationManager::clearCache();

        $student = Student::get(2);
        $this->assertCount(0, $student->courses);

        // Testing replacing all for one.
        RelationManager::clearCache();

        $student = Student::get(2);
        $student->courses->clear();

        $student->courses[] = Course::get(2);
        $student->save();

        RelationManager::clearCache();

        $student = Student::get(2);
        $this->assertCount(1, $student->courses);
    }


    /**
     * @covers \SweetORM\Entity
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\Database\Query
     * @covers \SweetORM\Database\QueryGenerator
     */
    public function testStripDownFetch()
    {
        Utilities::resetDatabase();

        $cat1 = Category::get(1)->data(['description', 'id']);
        $cat2 = Category::get(2)->data(['id']);
        $cat3 = Category::get(3)->data();
        $catnone = Category::get(999999);

        $this->assertEquals('Site news.', $cat1['description']);
        $this->assertEquals(1, $cat1['id']);
        $this->assertEquals(array('id' => 2), $cat2);
        $this->assertEquals(array('id' => '3','name' => 'FAQ','description' => 'FAQ Posts', 'created' => $cat3['created']), $cat3);
        $this->assertFalse($catnone);
    }
}
