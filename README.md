# SweetORM 
[![Build Status](https://travis-ci.org/tomvlk/sweet-orm.svg)](https://travis-ci.org/tomvlk/sweet-orm) [![Coverage Status](https://coveralls.io/repos/tomvlk/sweet-orm/badge.svg?branch=master&service=github)](https://coveralls.io/github/tomvlk/sweet-orm?branch=master) [![Codacy Badge](https://api.codacy.com/project/badge/grade/b90c424851234082a43a0c0c94de7922)](https://www.codacy.com/app/tomvalk/sweet-orm) [![Codacy Badge](https://api.codacy.com/project/badge/coverage/b90c424851234082a43a0c0c94de7922)](https://www.codacy.com/app/tomvalk/sweet-orm) [![Latest Stable Version](https://poser.pugx.org/tomvlk/sweet-orm/v/stable)](https://packagist.org/packages/tomvlk/sweet-orm) [![Latest Unstable Version](https://poser.pugx.org/tomvlk/sweet-orm/v/unstable)](https://packagist.org/packages/tomvlk/sweet-orm) [![License](https://poser.pugx.org/tomvlk/sweet-orm/license)](https://packagist.org/packages/tomvlk/sweet-orm) [![Build Time](https://buildtimetrend.herokuapp.com/badge/tomvlk/sweet-orm)](https://buildtimetrend.herokuapp.com/dashboard/tomvlk/sweet-orm)

Simple and PHP orm, without having to use command line generators and migrators, can work on your existing table structure.

## Configure and connecting
To start using the SweetORM you need to configure it, the whole back runs on PDO and needs to have a connection or configuration.

##### Inject PDO Connection
Only if you already have a PDO connection ready to the targetted database you could inject it.
```php
\SweetORM\ConnectionManager::injectConnection($pdo); // Where $pdo is an instance of PDO. Active connection!
```

##### Configuring for separate connection
When you don't have a PDO connection you need to setup the configuration of your database before touching any of the entities
```php
\SweetORM\Configuration::set('database_driver',   'pdo_mysql');     // No other drivers support right now
\SweetORM\Configuration::set('database_host',     'localhost');
\SweetORM\Configuration::set('database_port',     3306);            // Optional, default 3306
\SweetORM\Configuration::set('database_db',       'sweet_test');
\SweetORM\Configuration::set('database_user',     'root');
\SweetORM\Configuration::set('database_password', '');
```

## Defining Entities

Entities are PHP class declarations made up on your database table structure. You need to extend the abstract Entity in SweetORM and use the Annotations available in SweetORM.

```php
use SweetORM\Entity;
/**
 * @\SweetORM\Structure\Annotation\Entity
 * @Table(name="post")     <== The table has an attribute called 'name', which contains the table name in your database.
 */
class Post extends \SweetORM\Entity // Make sure you extend the SweetORM\Entity!
{
    /**
     * @Column(type="integer", primary=true, autoIncrement=true)
     */
    public $id;
    /**
     * @Column(type="integer")
     */
    public $authorid;
    /**
     * @Column(type="integer")
     */
    public $categoryid;
    /**
     * @var Category
     * @OneToOne(targetEntity="SweetORM\Tests\Models\Category")
     * @Join(column="categoryid", targetColumn="id")
     */
    public $category; // This will be a relation, the category holds a Category entity instance, lazy fetched from your 'categoryid' column!
    /**
     * @Column(type="string")
     */
    public $title;
    /**
     * @Column(type="string")
     */
    public $content;
}
```

Other example, defining the Category entity:

```php

/**
 * @\SweetORM\Structure\Annotation\Entity
 * @Table(name="category")
 */
class Category extends \SweetORM\Entity
{
    /**
     * @Column(type="integer", primary=true, autoIncrement=true)
     */
    public $id;
    /**
     * @Column(type="string")
     */
    public $name;
    /**
     * @Column(type="string")
     */
    public $description;

    // One To Many relationship
    /**
     * @var Post[]
     * @OneToMany(targetEntity="SweetORM\Tests\Models\Post")
     * @Join(column="id", targetColumn="categoryid")
     */
    public $posts;  // Will be available, and fetched when you refer to it using lazy loading.
}
```



## Reading from database

Reading and searching from the database is very easy, you have two options for reading.

#### Get by primary key
Get a single Entity you requested back with the primary key value you ask for. Easy and fast:
```php
$category = Category::get(1);

echo get_class($category); // Output: Category
echo $category->id; // Output: 1
```

#### Find by Query (Query Builder)
The ORM has a build-in query building option you could easy trigger with the static method find() on the Entity class
```php
$categories = Category::find()->all();
// $categories is now an array with all the categories in your database, all returned as Entity instances.
```

More information about the Query Builder will be available at the section [Query Building](#query-builder).

## Saving
Saving to the database is easy. To create a new instance of a entity, just create a new Entity object like this:

#### Creating new Entity
```php
$category = new Category();
```

Then set all the properties:
```php
$category->name = "Samples";
$category->description = "Sample posts";
```

And finally to save:
```php
$category->save(); // -> returns true or false, true on success, false on failure, will also throw exceptions.
```


#### Changing Entity
To change an existing or fetched entity, just change the property value and use ```save()``` again.

```php
$category = Category::get(1);
$category->name = "Samples - Old";
$category->save(); // -> returns true or false.
```



#### Delete Entity
To delete the entity from the database use the method ```delete()``` on the entity.

```php
$category = Category::get(1);

// Delete
$category->delete();
```



## Query Builder
For finding the entity you could use the Query Builder. This will interactively build a SQL Query without knowing the exact syntax for the database.

