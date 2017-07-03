<?php
/**
 * ConfigurationTest.php Description
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests;

use PHPUnit\Framework\TestCase;
use SweetORM\Configuration as Config;

/**
 * Class ConfigurationTest
 * @package SweetORM\Tests
 *
 * @coversDefaultClass \SweetORM\Configuration
 */
class ConfigurationTest extends TestCase
{

    /**
     * @covers ::set
     */
    public function testSet()
    {
        Config::set('test_key_set_1', true);
        Config::set('test_key_set_2', array(true));
        Config::set('test_key_set_3', "Value");

        $this->assertTrue(Config::get('test_key_set_1'));
        $this->assertEquals(array(true), Config::get('test_key_set_2'));
        $this->assertEquals("Value", Config::get('test_key_set_3'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        Config::set('test_key_get_1', true);
        Config::set('test_key_get_2', array(true));
        Config::set('test_key_get_3', "Value");

        $this->assertTrue(Config::get('test_key_get_1'));
        $this->assertEquals(array(true), Config::get('test_key_get_2'));
        $this->assertEquals("Value", Config::get('test_key_get_3'));
    }

    /**
     * @covers ::add
     */
    public function testAdd()
    {
        Config::set('test_key_add_1', array(true));

        Config::add('test_key_add_1', true);
        Config::add('test_key_add_1', false);

        $this->assertEquals(array(true,true,false), Config::get('test_key_add_1'));


        Config::set('test_key_add_2', array(true));

        Config::add('test_key_add_2', true);
        Config::add('test_key_add_2', array('on' => true));

        $this->assertEquals(array(true,true,array('on'=>true)), Config::get('test_key_add_2'));

        $result = Config::add('test_nonexisting_key', true);
        $this->assertFalse($result);
    }
}
