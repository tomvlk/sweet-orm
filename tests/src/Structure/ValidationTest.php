<?php
/**
 * Validation Test
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests\Structure;

use SweetORM\EntityManager;
use SweetORM\Structure\Validator\ValidationResult;
use \SweetORM\Tests\Models\Post;
use \SweetORM\Tests\Models\Category;

/**
 * Class ValidationTest
 * @package SweetORM\Tests\Structure
 *
 * @coversDefaultClass \SweetORM\Structure\Validator\Validator
 */
class ValidationTest extends \PHPUnit_Framework_TestCase
{

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


        $array3 = array(
            'name' => 'Test Name',
            'description' => 'Jaja'
        );

        $result3 = Category::validator($array3)->test();
        $this->assertInstanceOf(ValidationResult::class, $result3);
        $this->assertTrue($result3->isSuccess());
    }


}
