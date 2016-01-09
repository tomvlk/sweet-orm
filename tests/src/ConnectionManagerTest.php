<?php
/**
 * Connection Manager Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests;

use SweatORM\Configuration;
use SweatORM\ConnectionManager;

/**
 * Class ConnectionManagerTest
 * @package SweatORM\Tests
 *
 * @coversDefaultClass \SweatORM\ConnectionManager
 */
class ConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Prepare the Configuration by calling our TestHelper
        Utilities::injectDatabaseConfiguration();
    }

    /**
     * @covers ::getConnection
     * @covers ::clearConnection
     * @covers ::createConnection
     */
    public function testGetConnection()
    {
        // Test to get a connection
        // First we are going to clear the current connection
        ConnectionManager::clearConnection();

        $pdo = ConnectionManager::getConnection();

        $this->assertInstanceOf("\\PDO", $pdo);
    }

    /**
     * @covers ::getConnection
     * @covers ::clearConnection
     * @covers ::createConnection
     */
    public function testGetInvalidConnection()
    {
        ConnectionManager::clearConnection();
        Utilities::injectDatabaseConfiguration();
        Configuration::set('database_driver', null);

        // Try to make connection now, with no configurations
        try{
            ConnectionManager::getConnection();
            $this->assertTrue(false);
        } catch(\Exception $e) {
            $this->assertTrue(true);
        }


        // Again with no host
        ConnectionManager::clearConnection();
        Utilities::injectDatabaseConfiguration();
        Configuration::set('database_host', null);

        // Try to make connection now, with no configurations
        try{
            ConnectionManager::getConnection();
            $this->assertTrue(false);
        } catch(\Exception $e) {
            $this->assertTrue(true);
        }
    }

}
