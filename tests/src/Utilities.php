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

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE author;");

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE postchange;");

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE student;");

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE course;");

        $connection->exec(/** @lang MySQL */
            "TRUNCATE TABLE student_courses;");

        // Fill the test data
        $category = array(
            array('id' => '1','name' => 'News','description' => 'Site news.'),
            array('id' => '2','name' => 'Press','description' => 'Press news'),
            array('id' => '3','name' => 'FAQ','description' => 'FAQ Posts'),
            array('id' => '4','name' => 'Downloads','description' => 'Download Posts')
        );

        $authors = array(
            array('id' => '1', 'name' => 'First Author', 'email' => null),
            array('id' => '2', 'name' => 'Jan the Author', 'email' => 'jan.author@example.com'),
            array('id' => '3', 'name' => 'Eric Authorinus', 'email' => 'ericauth@example.com')
        );

        $posts = array(
            array('id' => '1', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #1', 'content' => 'Sample News 1'),
            array('id' => '2', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #2', 'content' => 'Sample News 2'),
            array('id' => '3', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #3', 'content' => 'Sample News 3'),
            array('id' => '4', 'authorid' => '1', 'categoryid' => '1', 'title' => 'Sample News #4', 'content' => 'Sample News 4'),
            array('id' => '5', 'authorid' => '2', 'categoryid' => '2', 'title' => 'Sample Press #1', 'content' => 'Sample Press 1'),
            array('id' => '6', 'authorid' => '2', 'categoryid' => '2', 'title' => 'Sample Press #2', 'content' => 'Sample Press 2'),
            array('id' => '7', 'authorid' => '2', 'categoryid' => '3', 'title' => 'Sample FAQ #1', 'content' => 'Sample FAQ 1'),
            array('id' => '8', 'authorid' => '3', 'categoryid' => '3', 'title' => 'Sample FAQ #2', 'content' => 'Sample FAQ 2'),
            array('id' => '9', 'authorid' => '3', 'categoryid' => '4', 'title' => 'Sample Downloads #1', 'content' => 'Sample Downloads 1'),
            array('id' => '10', 'authorid' => '3', 'categoryid' => '4', 'title' => 'Sample News #2', 'content' => 'Sample Downloads 2')
        );

        $courses = array(
            array('id' => '1', 'name' => 'Course #1', 'description' => null),
            array('id' => '2', 'name' => 'Course #2', 'description' => 'Optional Description')
        );

        $students = array(
            array('id' => '1', 'name' => 'Student #1'),
            array('id' => '2', 'name' => 'Student #2'),
            array('id' => '3', 'name' => 'Student #3'),
            array('id' => '4', 'name' => 'Student #4'),
            array('id' => '5', 'name' => 'Student #5'),
            array('id' => '6', 'name' => 'Student #6'),
            array('id' => '7', 'name' => 'Student #7'),
            array('id' => '8', 'name' => 'Student #8'),
            array('id' => '9', 'name' => 'Student #9'),
            array('id' => '10', 'name' => 'Student #10')
        );


        $student_courses = array(
            array('student_id' => 1, 'course_id' => 1),
            array('student_id' => 1, 'course_id' => 2),
            array('student_id' => 2, 'course_id' => 1),
            array('student_id' => 4, 'course_id' => 1),
            array('student_id' => 4, 'course_id' => 2),
            array('student_id' => 5, 'course_id' => 2),
            array('student_id' => 6, 'course_id' => 1),
            array('student_id' => 7, 'course_id' => 2),
            array('student_id' => 8, 'course_id' => 1),
            array('student_id' => 9, 'course_id' => 1),
            array('student_id' => 9, 'course_id' => 2),
            array('student_id' => 10, 'course_id' => 1),
            array('student_id' => 10, 'course_id' => 2)
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

        foreach ($authors as $row) {
            $sql = "INSERT INTO author (id, name, email) VALUES (?, ?, ?);";
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

        foreach ($courses as $row) {
            $sql = "INSERT INTO course (id, name, description) VALUES (?, ?, ?);";
            $query = $connection->prepare($sql);

            $idx = 1;
            foreach($row as $column => $value) {
                $query->bindValue($idx, $value);
                $idx++;
            }

            $query->execute();
        }

        foreach ($students as $row) {
            $sql = "INSERT INTO student (id, name) VALUES (?, ?);";
            $query = $connection->prepare($sql);

            $idx = 1;
            foreach($row as $column => $value) {
                $query->bindValue($idx, $value);
                $idx++;
            }

            $query->execute();
        }

        foreach ($student_courses as $row) {
            $sql = "INSERT INTO student_courses (student_id, course_id) VALUES (?, ?);";
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