<?php
/**
 * Utilities for Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests;

use SweatORM\Configuration;

class Utilities {

    public static function injectDatabaseConfiguration($driver = 'pdo_mysql')
    {
        if ($driver == 'pdo_mysql') {
            Configuration::set('database_driver',   'pdo_mysql');
            Configuration::set('database_host',     'localhost');
            Configuration::set('database_port',     3306);
            Configuration::set('database_db',       'sweat_test');
            Configuration::set('database_user',     'root');
            Configuration::set('database_password', '');
        }
    }
}