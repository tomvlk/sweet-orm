<?php
/**
 * Connection Manager, will manage the PDO encapsulated connections.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM;

/**
 * Hold connection instances for you.
 * @package SweatORM
 */
class ConnectionManager
{
    /**
     * Current holding connection
     * @var \PDO|null
     */
    private static $connection = null;

    /**
     * Get PDO Connection
     *
     * @return \PDO|null
     * @throws \Exception
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            self::createConnection();
        }

        return self::$connection;
    }

    /**
     * Inject already existing PDO Connection into the ORM.
     * Useful for existing frameworks or libraries.
     *
     * @param \PDO $connection
     *
     * @codeCoverageIgnore
     */
    public static function injectConnection(\PDO $connection)
    {
        self::$connection = $connection;
    }

    /**
     * Clear connection
     */
    public static function clearConnection()
    {
        self::$connection = null;
    }


    /**
     * Create PDO connection with current Configuration
     *
     * @throws \Exception
     */
    private static function createConnection()
    {
        // Validate if configuration has enough details.
        $driver = Configuration::get('database_driver');

        // Make DSN and options
        $dsn = "";
        $options = Configuration::get('database-options');
        $options = !is_array($options) ? array() : $options;

        $user = null;
        $password = null;

        if ($driver === 'pdo_sqlite') {
            $path = Configuration::get('database_path');

            if (! $path || ! file_exists($path)) {
                throw new \Exception("SQLite Database File doesn't exists or not configured! '".$path."'");
            }

            $dsn = "sqlite:" . $path;
        }else if ($driver === 'pdo_mysql') {
            $host = Configuration::get('database_host');
            $database = Configuration::get('database_db');
            $user = Configuration::get('database_user');
            $password = Configuration::get('database_password');

            $port = Configuration::get('database_port');
            $port = !is_int($port) ? 3306 : $port;

            $encoding = Configuration::get('database_encoding');
            $encoding = $encoding == null ? 'utf8' : $encoding;

            if (is_null($host) || is_null($database) || is_null($user) || is_null($password)) {
                throw new \Exception("Please configure the ORM first. We can't get a connection now!");
            }

            $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $database . ";charset=" . $encoding;
        }else{
            throw new \Exception("Please configure the ORM first. We can't get a connection now! No driver given!");
        }

        // Make connection
        self::$connection = new \PDO($dsn, $user, $password, $options);
    }

}