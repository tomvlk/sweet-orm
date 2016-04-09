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
/**
 * @EntityClass
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
 * @EntityClass
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

### Selecting Entity
Selecting entity with several query builder operations:

```php
$category = Category::find()->where('name', 'News')->one();
```
This is the same as the following query:
```sql
SELECT * FROM category WHERE name = 'News' LIMIT 1;
```

### Builder Methods
Methods to build your query:

```php
$query = Category::find();

$query->select();
$query->select('id name'); // Only fetch id and name column (not property!).

$query->from('table'); // Select from table (not needed when using entity!)

$query->where('column', '=', 'value'); // Middle is optional, default '='.
$query->where(array('column' => 'value', 'column2' => array('=' => 'vallue2'))); // Complex where.

$query->limit(50); // Limit by 50 rows
$query->offset(10); // Offset by 10.

$query->all(); // Execute query, fetchAll.
$query->one(); // Execute query, fetch first row.


// Write,update,delete
$query->insert('table'); // Insert into table.
$query->into('table'); // optional!
$query->values(array('column' => 'value')); // Set insert data.
$query->apply(); // boolean

$query->update('table');
$query->set(array('column' => 'value')); // Set new data.
$query->where('id', 1);
$query->apply();

$query->delete('table');
$query->where('id', 1);
$query->apply();
```

Of course you can chain all operations on the query builder.


## Validation and filling
Validation against null and non-null, types and constraints (defined as annotations) could make using the ORM in REST environments way better.

### Define constraints
```php
class Test extends Entity {
    /**
     * @var string
     * @Column(type="string", null=true)
     * @Constraint(
     *     startsWith="www.",           <== Constraint for starting string.
     *     minLength=20                 <== Minimum string length.
     * )
     */
    public $longUrl;
}
```

You can use the following constraints:

- minLength: Minimum character length. Number.
- maxLength: Maximum character length. Number.
- minValue: Minimum int/float/double value to test against. Number.
- maxValue: Maximum int/float/double value to test against. Number.
- valid: Validate special cases. Can be 'email', 'url'. String.
- regex: Regular expression, will be tested against preg_match(). String.
- enum: Must be in the array given. Array of strings.
- startsWith: Test if string starts with. String
- endsWith: Test if string ends with. String


### Test constraints and requirements

```php
    // We are going to use the ArrayValidator (will be selected automatically)
    $data = array(
        'longUrl' => 'www.verylongurlthatvalidatescorrectly.com'
    );
    $result = Test::validator($data)->test();

    $result->isSuccess(); // true
    $result->getErrors(); // empty array.
```


### Filling and creating entities with raw data

Creating from the validator:
```php
    // We are going to use the ArrayValidator (will be selected automatically)
    $data = array(
        'longUrl' => 'www.verylongurlthatvalidatescorrectly.com'
    );
    $entity = Test::validator($data)->create(); // Entity = instance of Test.

    $entity->save(); // true
```


Update with data provided by the validator
```php
    // We are going to use the ArrayValidator (will be selected automatically)
    $entity = Test::get(1); // Instance of already existing test entity.

    $data = array(
        'longUrl' => 'www.verylongurlthatvalidatescorrectly2.com'
    );
    Test::validator($data)->fill($entity); // Entity = instance of Test.

    $entity->save(); // true, is now updated!
```
_Remember, by default the primary key(s) can't be overwritten by validator data!_
