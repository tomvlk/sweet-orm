<?php
/**
 * Validation Test
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Structure;

use PHPUnit\Framework\TestCase;
use SweetORM\EntityManager;
use SweetORM\Structure\Validator\ValidationResult;
use SweetORM\Tests\Models\ConstraintTest;
use SweetORM\Tests\Models\Category;
use SweetORM\Tests\Models\Student;
use SweetORM\Tests\Models\TypeTest;

/**
 * Class ValidationTest
 * @package SweetORM\Tests\Structure
 *
 * @coversDefaultClass \SweetORM\Structure\Validator\Validator
 */
class ValidationTest extends TestCase
{

    /**
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\EntityManager::getInstance
     * @covers \SweetORM\EntityManager::getEntityStructure
     * @covers \SweetORM\EntityManager::validator
     * @covers \SweetORM\Entity::validator
     * @covers \SweetORM\Structure\Indexer\ColumnIndexer
     * @covers \SweetORM\Structure\EntityStructure
     * @covers \SweetORM\Structure\ValidationManager
     * @covers \SweetORM\Structure\ValidationManager::validator
     * @covers \SweetORM\Structure\Validator\Validator
     * @covers \SweetORM\Structure\Validator\ArrayValidator
     * @covers \SweetORM\Structure\Annotation\Constraint
     * @covers \SweetORM\Database\Query
     */
    public function testArrayValidation()
    {
        $manager = EntityManager::getInstance();
        $manager->clearRegisteredEntities();

        $array1 = array(
            'tests' => 'nope'
        );

        $result1 = Category::validator($array1)->test();

        $this->assertInstanceOf(ValidationResult::class, $result1);
        $this->assertFalse($result1->isSuccess());
        $this->assertCount(2, $result1->getErrors());


        $array2 = array(
            'id' => 55,
            'name' => 0,
            'description' => null
        );

        $result2 = Category::validator($array2)->test();

        $this->assertInstanceOf(ValidationResult::class, $result2);
        $this->assertFalse($result2->isSuccess());
        $this->assertCount(2, $result2->getErrors());
        $this->assertContains('Must be \'string\'', $result2->getErrors()[0]);

        // Validate valid array.
        $array3 = array(
            'name' => 'Test Name',
            'description' => 'Jaja'
        );

        $result3 = Category::validator($array3)->test();
        $this->assertInstanceOf(ValidationResult::class, $result3);
        $this->assertTrue($result3->isSuccess());

        // Validate invalid email.
        $array4 = array(
            'name' => 'Henk',
            'email' => 'invalid'
        );
        $result4 = Student::validator($array4)->test();
        $this->assertInstanceOf(ValidationResult::class, $result4);
        $this->assertFalse($result4->isSuccess());

        // Validate valid email.
        $array5 = array(
            'name' => 'Henk',
            'email' => 'invalid@valid.com'
        );
        $result5 = Student::validator($array5)->test();
        $this->assertInstanceOf(ValidationResult::class, $result5);
        $this->assertTrue($result5->isSuccess());

        // Validate invalid date.
        $array6 = array(
            'name' => 'Henk',
            'description' => '1234test',
            'created' => 'invalid date'
        );
        $result6 = Category::validator($array6)->test();
        $this->assertInstanceOf(ValidationResult::class, $result6);
        $this->assertFalse($result6->isSuccess());

        // Validate string date.
        $array7 = array(
            'name' => 'Henk',
            'description' => '1234test',
            'created' => date('c')
        );
        $result7 = Category::validator($array7)->test();
        $this->assertInstanceOf(ValidationResult::class, $result7);
        $this->assertTrue($result7->isSuccess());

        // Validate numeric date.
        $array8 = array(
            'name' => 'Henk',
            'description' => '1234test',
            'created' => time()
        );
        $result8 = Category::validator($array8)->test();
        $this->assertInstanceOf(ValidationResult::class, $result8);
        $this->assertTrue($result8->isSuccess());


        // Type Testing
        $array9 = array(
            'id' => intval('1'), // String integer! Should be converted before validation.
            'string' => 'correct', // Valid!
            'text' => 'correct', // Valid!
            'double' => 1.22, // Valid!
            'float' => floatval('1.5345345'), // Same as string integer, convert first!
            'boolean' => true // Valid!
        );
        $result9 = TypeTest::validator($array9)->test();
        $this->assertTrue($result9->isSuccess());
    }



    /**
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\EntityManager::getInstance
     * @covers \SweetORM\EntityManager::getEntityStructure
     * @covers \SweetORM\EntityManager::validator
     * @covers \SweetORM\Structure\Indexer\ColumnIndexer
     * @covers \SweetORM\Entity::validator
     * @covers \SweetORM\Structure\EntityStructure
     * @covers \SweetORM\Structure\ValidationManager
     * @covers \SweetORM\Structure\ValidationManager::validator
     * @covers \SweetORM\Structure\Validator\Validator
     * @covers \SweetORM\Structure\Validator\ArrayValidator
     * @covers \SweetORM\Structure\Annotation\Constraint
     * @covers \SweetORM\Database\Query
     */
    public function testArrayFilling()
    {
        $manager = EntityManager::getInstance();
        $manager->clearRegisteredEntities();

        $array1 = array(
            'tests' => 'nope'
        );

        $entity1 = new Category();
        $result1 = Category::validator($array1)->fill($entity1);

        $this->assertInstanceOf(ValidationResult::class, $result1);
        $this->assertFalse($result1->isSuccess());
        $this->assertCount(2, $result1->getErrors());


        $array2 = array(
            'name' => 'Test Category, Filling in.',
            'description' => 'Description is filled in!'
        );
        $entity2 = new Category();
        Category::validator($array2)->fill($entity2);
        $this->assertInstanceOf(Category::class, $entity2);
        $this->assertEquals('Test Category, Filling in.', $entity2->name);
        $this->assertEquals('Description is filled in!', $entity2->description);

        $save2 = $entity2->save();
        $this->assertTrue($save2);

        $delete2 = $entity2->delete();
        $this->assertTrue($delete2);


        $entity3 = Category::validator($array2)->create();
        $this->assertInstanceOf(Category::class, $entity3);
        $this->assertEquals('Test Category, Filling in.', $entity3->name);
        $this->assertEquals('Description is filled in!', $entity3->description);
    }


    /**
     * @covers \SweetORM\EntityManager
     * @covers \SweetORM\EntityManager::getInstance
     * @covers \SweetORM\EntityManager::getEntityStructure
     * @covers \SweetORM\EntityManager::validator
     * @covers \SweetORM\Entity::validator
     * @covers \SweetORM\Structure\EntityStructure
     * @covers \SweetORM\Structure\ValidationManager
     * @covers \SweetORM\Structure\ValidationManager::validator
     * @covers \SweetORM\Structure\Validator\Validator
     * @covers \SweetORM\Structure\Validator\ArrayValidator
     * @covers \SweetORM\Structure\Annotation\Constraint
     * @covers \SweetORM\Structure\Indexer\ColumnIndexer
     * @covers \SweetORM\Database\Query
     */
    public function testConstraints()
    {
        $manager = EntityManager::getInstance();
        $manager->clearRegisteredEntities();

        $array = array(
            'startsWith' => 'www.google.com' // Is not above 20! Fail!
        );
        $result = ConstraintTest::validator($array)->test();
        $this->assertFalse($result->isSuccess());


        $array = array(
            'startsWith' => 'www.google.com/testi' // Is 20, success!
        );
        $result = ConstraintTest::validator($array)->test();
        $this->assertTrue($result->isSuccess());

        $array = array(
            'startsWith' => 'www.google.com/testi' // Is 20 length
        );
        $result = ConstraintTest::validator($array)->test();
        $this->assertTrue($result->isSuccess());

        $array = array(
            'startsWith' => 'www.google.com/testin' // Is 21 length
        );
        $result = ConstraintTest::validator($array)->test();
        $this->assertTrue($result->isSuccess());

        $array = array(
            'startsWith' => 'ww.google.com/testin' // Is 20 length but doesnt start with www. => fail!
        );
        $result = ConstraintTest::validator($array)->test();
        $this->assertFalse($result->isSuccess());



        $result = ConstraintTest::validator(array('question' => 'noo'))->test();
        $this->assertFalse($result->isSuccess());

        $result = ConstraintTest::validator(array('question' => 'YES'))->test();
        $this->assertFalse($result->isSuccess());

        $result = ConstraintTest::validator(array('question' => 'no'))->test();
        $this->assertTrue($result->isSuccess());

        $result = ConstraintTest::validator(array('question' => 'yes'))->test();
        $this->assertTrue($result->isSuccess());



        $result = ConstraintTest::validator(array('between' => 44.49))->test();
        $this->assertFalse($result->isSuccess());

        $result = ConstraintTest::validator(array('between' => 44.5))->test();
        $this->assertTrue($result->isSuccess());

        $result = ConstraintTest::validator(array('between' => 55.5))->test();
        $this->assertTrue($result->isSuccess());

        $result = ConstraintTest::validator(array('between' => 55.51))->test();
        $this->assertFalse($result->isSuccess());


        $result = ConstraintTest::validator(array('endsWith' => 'www.test.com'))->test();
        $this->assertTrue($result->isSuccess());

        $result = ConstraintTest::validator(array('endsWith' => 'www.whereisgoogle.com'))->test();// == 21 chars
        $this->assertFalse($result->isSuccess());

        $result = ConstraintTest::validator(array('endsWith' => 'www.hereisgoogle.com'))->test();// == 20 chars
        $this->assertTrue($result->isSuccess());

        $result = ConstraintTest::validator(array('endsWith' => 'www.test.nl'))->test(); // != .com
        $this->assertFalse($result->isSuccess());


        $result = ConstraintTest::validator(array('youtube' => 'www.test.nl'))->test();
        $this->assertFalse($result->isSuccess());

        $result = ConstraintTest::validator(array('youtube' => 'https://youtu.be'))->test();
        $this->assertFalse($result->isSuccess());

        $result = ConstraintTest::validator(array('youtube' => 'https://youtu.be/dQw4w9WgXcQ'))->test();
        $this->assertTrue($result->isSuccess());


        $result = ConstraintTest::validator(array('url' => 'https://youtu.be/dQw4w9WgXcQ'))->test();
        $this->assertTrue($result->isSuccess());

        $result = ConstraintTest::validator(array('url' => 'dQw4w9WgXcQ'))->test();
        $this->assertFalse($result->isSuccess());

    }
}
