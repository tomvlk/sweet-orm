<?php
/**
 * Utilities for Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Tests;

use SweatORM\Configuration;
use SweatORM\ConnectionManager;

class Utilities {

    private static $dbKeywords = array('ALTER', 'CREATE', 'DELETE', 'DROP', 'INSERT', 'REPLACE', 'SELECT', 'SET', 'TRUNCATE', 'UPDATE', 'USE', 'DELIMITER', 'END');

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


    /**
     * Reset Database content to sample content
     */
    public static function resetDatabase()
    {
        $connection = ConnectionManager::getConnection();

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE post;");

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE category;");

        // Fill the test data
        $category = array(
            array('id' => '1','name' => 'News','description' => 'Site news.'),
            array('id' => '2','name' => 'Press','description' => 'Press news'),
            array('id' => '3','name' => 'FAQ','description' => 'FAQ Posts'),
            array('id' => '4','name' => 'Downloads','description' => 'Download Posts')
        );

        $posts = array(
            array('id' => '1', 'author' => '1', 'category' => '1', 'title' => 'Sample News #1', 'content' => 'Sample News 1'),
            array('id' => '2', 'author' => '1', 'category' => '1', 'title' => 'Sample News #2', 'content' => 'Sample News 2'),
            array('id' => '3', 'author' => '1', 'category' => '1', 'title' => 'Sample News #3', 'content' => 'Sample News 3'),
            array('id' => '4', 'author' => '1', 'category' => '1', 'title' => 'Sample News #4', 'content' => 'Sample News 4'),
            array('id' => '5', 'author' => '1', 'category' => '2', 'title' => 'Sample Press #1', 'content' => 'Sample Press 1'),
            array('id' => '6', 'author' => '1', 'category' => '2', 'title' => 'Sample Press #2', 'content' => 'Sample Press 2'),
            array('id' => '7', 'author' => '1', 'category' => '3', 'title' => 'Sample FAQ #1', 'content' => 'Sample FAQ 1'),
            array('id' => '8', 'author' => '1', 'category' => '3', 'title' => 'Sample FAQ #2', 'content' => 'Sample FAQ 2'),
            array('id' => '9', 'author' => '1', 'category' => '4', 'title' => 'Sample Downloads #1', 'content' => 'Sample Downloads 1'),
            array('id' => '10', 'author' => '1', 'category' => '4', 'title' => 'Sample News #2', 'content' => 'Sample Downloads 2')
        );

        foreach ($category as $row) {
            $sql = "INSERT INTO category (id, name, description) VALUES (?, ?, ?);";
            $query = $connection->prepare($sql);

            $idx = 1;
            foreach($row as $column => $value) {
                $query->bindValue($idx, $value);
                $idx++;
            }

            $query->execute();
        }


        foreach ($posts as $row) {
            $sql = "INSERT INTO post (id, author, category, title, content) VALUES (?, ?, ?, ?, ?);";
            $query = $connection->prepare($sql);

            $idx = 1;
            foreach($row as $column => $value) {
                $query->bindValue($idx, $value);
                $idx++;
            }

            $query->execute();
        }
    }
}