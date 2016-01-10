<?php
/**
 * Utilities for Tests
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Tests;

use SweetORM\Configuration;
use SweetORM\ConnectionManager;
use SweetORM\Structure\RelationManager;

class Utilities {

    public static function injectDatabaseConfiguration($driver = 'pdo_mysql')
    {
        if ($driver == 'pdo_mysql') {
            Configuration::set('database_driver',   'pdo_mysql');
            Configuration::set('database_host',     'localhost');
            Configuration::set('database_port',     3306);
            Configuration::set('database_db',       'sweet_test');
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

        // Clear Relation cache
        RelationManager::clearCache();

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
            array('id' => '1', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #1', 'content' => 'Sample News 1'),
            array('id' => '2', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #2', 'content' => 'Sample News 2'),
            array('id' => '3', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #3', 'content' => 'Sample News 3'),
            array('id' => '4', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #4', 'content' => 'Sample News 4'),
            array('id' => '5', 'authorid' => '1', 'categoryid' => '2', 'title' => 'Sample Press #1', 'content' => 'Sample Press 1'),
            array('id' => '6', 'authorid' => '1', 'categoryid' => '2', 'title' => 'Sample Press #2', 'content' => 'Sample Press 2'),
            array('id' => '7', 'authorid' => '1', 'categoryid' => '3', 'title' => 'Sample FAQ #1', 'content' => 'Sample FAQ 1'),
            array('id' => '8', 'authorid' => '1', 'categoryid' => '3', 'title' => 'Sample FAQ #2', 'content' => 'Sample FAQ 2'),
            array('id' => '9', 'authorid' => '1', 'categoryid' => '4', 'title' => 'Sample Downloads #1', 'content' => 'Sample Downloads 1'),
            array('id' => '10', 'authorid' => '1', 'categoryid' => '4', 'title' => 'Sample News #2', 'content' => 'Sample Downloads 2')
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
            $sql = "INSERT INTO post (id, authorid, categoryid, title, content) VALUES (?, ?, ?, ?, ?);";
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