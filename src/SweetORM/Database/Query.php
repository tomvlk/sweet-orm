<?php
/**
 * Query Builder for ORM
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Database;
use SweetORM\ConnectionManager;
use SweetORM\Entity;
use SweetORM\EntityManager;
use SweetORM\Exception\ORMException;
use SweetORM\Exception\QueryException;
use SweetORM\Structure\Annotation\Column;
use SweetORM\Structure\EntityStructure;

/**
 * Query Building on Entities
 *
 * @package SweetORM\Database
 */
class Query
{
    const QUERY_SELECT = 1;
    const QUERY_INSERT = 2;
    const QUERY_UPDATE = 3;
    const QUERY_DELETE = 4;

    const QUERY_CUSTOM = 10;

    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $verify;

    /**
     * @var EntityStructure
     */
    private $structure;

    /**
     * Hold last exception (chain)
     * @var QueryException
     */
    private $exception = null;

    /**
     * Holds the full query after building is complete
     *
     * @var string
     */
    private $query = "";

    /**
     * @var null|int One of the QUERY_* types
     */
    private $queryType = null;

    /* ===== Query Parts ===== */
    private $start = "";
    private $table = "";
    private $data = "";
    private $where = "";
    private $order = "";
    private $limit = "";


    /* ===== Helping Variables ===== */
    /** @var Column[] $columnOrder Will hold the column order for inserting */
    private $columnOrder = array();

    /* ===== Building Variables ===== */
    /** @var array */
    private $whereConditions = array();
    /** @var null|int */
    private $limitCount = null;
    /** @var null|int */
    private $limitOffset = null;
    /** @var null|string */
    private $sortBy = null;
    /** @var null|string */
    private $sortOrder = null;
    /** @var array */
    private $changeData = array();

    /* ===== Storage for Binding ===== */
    private $bindValues = array();
    private $bindTypes = array();


    /**
     * Query Builder constructor.
     *
     * @param string|Entity $entityClass
     * @param bool $autoSelect Automatically do the select and from based on the entity.
     * @param bool $verify Verify columns, true by default, false NOT  recommended!
     *
     * @throws ORMException
     */
    public function __construct($entityClass, $autoSelect = true, $verify = true)
    {
        $this->verify = $verify;

        if ($entityClass instanceof Entity) {
            $reflection = new \ReflectionClass($entityClass); // @codeCoverageIgnore
            $entityClass = $reflection->getName(); // @codeCoverageIgnore
        }

        $this->class = $entityClass;
        $this->structure = EntityManager::getInstance()->getEntityStructure($entityClass);
        if ($this->structure === false) {
            throw new QueryException("Could not construct Query Builder, entity not indexed correctly!"); // @codeCoverageIgnore
        }

        $this->generator = new QueryGenerator();

        // Automaticly set select and from based on the structure of the Entity
        if ($autoSelect) {
            $this->select("*");
            $this->from($this->structure->tableName);
        }
    }


    /**
     * Fake select method, used to maximize compatibility
     * @param string $columns
     * @return Query $this
     */
    public function select($columns = "*")
    {
        $this->start = $columns;
        $this->queryType = self::QUERY_SELECT;
        return $this;
    }

    /**
     * @param string $table
     * @return Query $this
     */
    public function from($table)
    {
        $this->table = $table;
        $this->queryType = self::QUERY_SELECT;
        return $this;
    }

    /**
     * Start insert mode
     *
     * @param string $table Table name
     * @return Query $this
     */
    public function insert($table = "")
    {
        $this->start = "";
        $this->queryType = self::QUERY_INSERT;
        $this->into($table);
        return $this;
    }

    /**
     * Insert into table
     *
     * @param string $table
     * @return Query $this
     */
    public function into($table = "")
    {
        if (empty($table)) {
            $table = $this->structure->tableName;
        }

        $this->table = $table;
        $this->queryType = self::QUERY_INSERT;

        // Determinate the column order
        $this->columnOrder = array();
        foreach ($this->structure->columns as $column) {
            $this->columnOrder[] = $column;
        }

        return $this;
    }

    /**
     * Update table
     *
     * @param string $table
     * @return Query $this
     */
    public function update($table = "")
    {
        if (empty($table)) {
            $table = $this->structure->tableName;
        }

        $this->table = $table;
        $this->queryType = self::QUERY_UPDATE;

        // Determinate the column order
        $this->columnOrder = array();
        foreach ($this->structure->columns as $column) {
            $this->columnOrder[] = $column;
        }

        return $this;
    }

    /**
     * Set mode to Delete
     *
     * @param string $table Table name
     * @return Query $this
     */
    public function delete($table = "")
    {
        if (empty($table)) {
            $table = $this->structure->tableName; // @codeCoverageIgnore
        }

        $this->table = $table;
        $this->queryType = self::QUERY_DELETE;

        return $this;
    }


    /**
     * Execute custom query.
     *
     * @param string $sql
     * @param array $bind
     *
     * @throws QueryException
     * @throws \PDOException
     *
     * @return Entity[]
     */
    public function custom($sql, $bind = array())
    {
        // Prepare query
        $query = ConnectionManager::getConnection()->prepare($sql);

        // Bind when needed
        if (count($bind) > 0) {
            $numericBinding = true;

            // Check if we are going to use the numeric binding
            foreach($bind as $key => $value) {
                if (is_int($key)) {
                    continue;
                }else{
                    $numericBinding = false;
                    if (substr($key, 0, 1) !== ':') {
                        throw new QueryException("When binding, you should use numeric keys or keys with : before each key to identify the binding place in the query!");
                    }
                }
            }

            // Bind it!
            $idx = 1;
            foreach($bind as $key => $value) {
                if ($numericBinding) {
                    $query->bindValue($idx, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR); // TODO: Better type detection..
                } else {
                    $query->bindValue($key, $value);
                }
            }
        }

        // Set fetch mode
        $query->setFetchMode(\PDO::FETCH_CLASS, $this->class);

        // Execute
        $query->execute();

        // Fetch and return
        return $this->injectState($query->fetchAll());
    }









    /**
     * Set data for inserting or updating
     *
     * @param array $data
     * @return Query $this
     */
    public function set($data)
    {
        if ($this->queryType !== self::QUERY_INSERT && $this->queryType !== self::QUERY_UPDATE) {
            $this->exception = new QueryException("When using set/data you must do a insert into or update first! Query is not in UPDATE or INSERT mode!", 0, $this->exception); // @codeCoverageIgnore
            return $this; // @codeCoverageIgnore
        }
        if (! is_array($data)) {
            $this->exception = new QueryException("set()/data() must have an array data parameter!", 0, $this->exception); // @codeCoverageIgnore
            return $this; // @codeCoverageIgnore
        }

        // Prepare the data
        $this->data = "";
        $this->changeData = array();

        // Verify and generate insert parts
        foreach ($this->columnOrder as $currentColumn) {
            // Verify if all the columns that are non-null exists when inserting
            if ($this->queryType === self::QUERY_INSERT) {
                // We MUST fill in the non null columns, with exception on the auto increment column
                if (! $currentColumn->null && ! $currentColumn->autoIncrement) {
                    if (! isset($data[$currentColumn->name])) {
                        $this->exception = new QueryException("Inserting data failed, data must contain all non-null columns defined in the entity!", 0, $this->exception);
                        return $this;
                    }
                }
            }

            // Skip primary key when updating.
            if ($this->queryType === self::QUERY_UPDATE && $currentColumn->primary) {
                // Skip
                continue;
            }

            // If data exists for current column
            if (isset($data[$currentColumn->name])) {
                $value = $data[$currentColumn->name];

                // Current column exists, save data
                $this->changeData[$currentColumn->name] = $value;
            } else {
                // We will insert NULL in this column, it's not given in the $data array
                $this->changeData[$currentColumn->name] = null;
            }
        }

        return $this;
    }

    /**
     * Set data for inserting or updating
     *
     * @param array $data
     * @return Query $this
     */
    public function values($data)
    {
        return $this->set($data);
    }



    /**
     * Add where conditions, you can give the conditions in multiple styles:
     * 1. -> where('id', 1)                    for id= 1 condition
     * 2. -> where('id', '=', 1)               for id = 1 condition
     * 3. -> where('id', 'IN', array(1, 2))    for id IN (1,2) condition
     * 4. -> where(array('id' => 1))           same as first style
     * 5. -> where(array('id' => array('=' => 1)) same as second style
     *
     * @param string|array $criteria String with column name, or array with condition for full where syntax
     *
     * @param string|null $operator Only when using column name in first parameter, fill this by the value when comparing
     * or fill in the operator used to compare
     *
     * @param string|null $value Only when using column name in first parameter and filling in the operator value.
     *
     * @return Query $this Return chained query.
     */
    public function where($criteria, $operator = null, $value = null)
    {
        // Check if we are in select or update mode
        if ($this->queryType !== self::QUERY_SELECT && $this->queryType !== self::QUERY_UPDATE && $this->queryType !== self::QUERY_DELETE) {
            $this->exception = new QueryException("When doing a where() we must be in SELECT or UPDATE mode!", 0, $this->exception);
            return $this;
        }

        // If the operator is the value, then we are going to use the = operator
        if (! is_array($criteria) && $value === null && $this->validValue($operator, "=") && func_num_args() === 2) {
            // Operator is now value!
            $criteria = array($criteria => array("=" => $operator));
        }

        // If it's the shorthand of the where, convert it to the normal criteria.
        if (! is_array($criteria) && $this->validOperator($operator) && $this->validValue($value, $operator)) {
            $criteria = array($criteria => array($operator => $value));
        }

        // No smart solution found!
        if (! is_array($criteria)) {
            $this->exception = new QueryException("The criteria in the where is invalid! Please look at the docs for the correct syntax!", 0, $this->exception);
            return $this;
        }

        // Get column names of table
        $columnNames = $this->structure->columnNames;

        // Parse criteria, validate and add to the current where clause.
        foreach ($criteria as $column => $compare) {
            // If using shorthand for = compare
            if (! is_array($compare)) {
                $criteria[$column] = array('=' => $compare);
                $compare = array('=' => $compare);
            }

            // Validate compare, validate column name
            if ($this->verify && ! in_array($column, $columnNames)) {
                $this->exception = new QueryException("Trying to prepare a where with column condition for a undefined column!", 0, $this->exception);
                continue;
            }

            $operator = array_keys($compare);
            $operator = $operator[0];
            $value = $compare[$operator];

            if ($this->validOperator($operator) && $this->validValue($value, $operator)) {
                // Add to the Query Where stack
                $this->whereConditions[] = array(
                    'column' => $column,
                    'operator' => $operator,
                    'value' => $value
                );
            }
            // Skip if not valid.
        }
        return $this;
    }


    /**
     * Limit the result
     *
     * @param int $limit Give the number of limited entities returned.
     * @return Query $this The current query stack.
     */
    public function limit($limit)
    {
        if ($this->queryType !== self::QUERY_SELECT) {
            $this->exception = new QueryException("Limit can only be applied on SELECT mode!", 0, $this->exception);
            return $this;
        }
        if (! is_int($limit) || $limit < 0) {
            $this->exception = new QueryException("Limit value should be an positive integer!", 0, $this->exception);
            return $this;
        }
        $this->limitCount = intval($limit);

        return $this;
    }

    /**
     * Offset the results
     *
     * @param int $offset Give the number of offset applied to the results.
     * @return Query $this The current query stack.
     */
    public function offset($offset)
    {
        if ($this->queryType !== self::QUERY_SELECT) {
            $this->exception = new QueryException("Offset can only be applied on SELECT mode!", 0, $this->exception);
            return $this;
        }
        if (! is_int($offset) || $offset < 0) {
            $this->exception = new QueryException("Offset value should be an positive integer!", 0, $this->exception);
            return $this;
        }
        $this->limitOffset = intval($offset);

        return $this;
    }


    /**
     * Sort by column value, Ascending or descending
     * @param string $column Column name to order with.
     * @param string $type Either ASC or DESC for the order type.
     * @return Query $this The current query stack.
     */
    public function sort($column, $type = 'ASC')
    {
        if ($this->queryType !== self::QUERY_SELECT) {
            $this->exception = new QueryException("Sorting can only be applied on SELECT mode!", 0, $this->exception);
            return $this;
        }

        // First lets upper the type.
        $type = strtoupper($type);

        if (! is_string($column)) {
            $this->exception = new QueryException("Sorting column must be an string!", 0, $this->exception);
        }

        if (! $this->validOrderType($type)) {
            $this->exception = new QueryException("Sorting requires a type that is either 'ASC' or 'DESC'!", 0, $this->exception);
            return $this;
        }

        // Validate the column
        if (in_array($column, $this->structure->columnNames)) {
            $this->sortBy = $column;
            $this->sortOrder = $type;
        }

        return $this;
    }


    /**
     * Execute Query and fetch all records as entities
     * @return false|\SweetORM\Entity[] Entities as successful result or false on not found.
     * @throws QueryException
     */
    public function all()
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->fetch(true);
    }

    /**
     * Execute Query and fetch a single record as entity
     *
     * @return Entity[]|false Entities as successful result or false on not found.
     * @throws QueryException
     */
    public function one()
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->fetch(false);
    }

    /**
     * Execute and apply changes, only for query mode INSERT and UPDATE!
     *
     * @throws QueryException
     * @throws \Exception
     *
     * @return int|bool Inserted ID when inserting or true/false when updating
     */
    public function apply()
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->execute();
    }


    /**
     * @param $multi
     * @return array|mixed
     *
     * @throws \Exception
     */
    private function fetch($multi)
    {
        // Let the generator do his work now,
        if ($this->queryType !== self::QUERY_SELECT) {
            throw new QueryException("Can only do all() or one() on SELECT mode!", 0, $this->exception);
        }

        $this->bindValues = array();
        $this->bindTypes = array();

        // Where
        $this->generator->generateWhere($this->whereConditions, $this->where, $this->bindValues, $this->bindTypes);

        // Order
        $this->generator->generateOrder($this->sortBy, $this->sortOrder, $this->order);

        // Limit
        $this->generator->generateLimit($this->limitCount, $this->limitOffset, $this->limit);


        // Combine parts
        $this->query = "";
        $this->combineQuery();

        // Get connection and prepare
        $connection = ConnectionManager::getConnection();
        $query = $connection->prepare($this->query);

        // Bind all values
        $idx = 1;
        foreach ($this->bindValues as $key => $value) {
            $query->bindValue($idx, $value, $this->bindTypes[$key]);
            $idx++;
        }

        // Set fetch mode
        $query->setFetchMode(\PDO::FETCH_CLASS, $this->class);

        // Execute
        $query->execute();

        // Fetch and return
        if ($multi) {
            return $this->injectState($query->fetchAll());
        }
        return $this->injectState($query->fetch());
    }


    /**
     * Execute the Query (used for INSERT and UPDATE)
     *
     * @throws QueryException
     * @throws \Exception
     * @return int|bool Inserted ID when inserting or true/false when updating
     */
    private function execute()
    {
        // Let the generator do his work now,
        if ($this->queryType !== self::QUERY_INSERT && $this->queryType !== self::QUERY_UPDATE && $this->queryType !== self::QUERY_DELETE) {
            throw new QueryException("Can only do apply() on INSERT and UPDATE mode!", 0, $this->exception); // @codeCoverageIgnore
        }

        // Validate all variables
        if ($this->queryType === self::QUERY_INSERT) {
            // We should have the table, all required data.
            if (count($this->changeData) === 0 || empty($this->table)) {
                throw new QueryException("When inserting you should at least give a table and the inserting data!", 0, $this->exception); // @codeCoverageIgnore
            }
        }
        if ($this->queryType === self::QUERY_UPDATE) {
            // We should have a where with at least one condition, data, and a table
            if (count($this->whereConditions) === 0 || count($this->changeData) === 0 || empty($this->table)) {
                throw new QueryException("When updating you should at least give a table, at least one where condition and the updating data!", 0, $this->exception); // @codeCoverageIgnore
            }
        }
        if ($this->queryType === self::QUERY_DELETE) {
            // We should have a where and table
            if (count($this->whereConditions) === 0 || empty($this->table)) {
                throw new QueryException("When deleting you should at least give a table and at least one where condition!", 0, $this->exception); // @codeCoverageIgnore
            }
        }

        $this->bindValues = array();
        $this->bindTypes = array();

        // Generate parts
        if ($this->queryType === self::QUERY_INSERT) {
            $this->generator->generateInsert($this->columnOrder, $this->changeData, $this->start, $this->data, $this->bindValues, $this->bindTypes);
        }

        if ($this->queryType === self::QUERY_UPDATE) {
            $this->generator->generateUpdate($this->changeData, $this->data, $this->bindValues, $this->bindTypes);
            $this->generator->generateWhere($this->whereConditions, $this->where, $this->bindValues, $this->bindTypes);
        }

        if ($this->queryType === self::QUERY_DELETE) {
            $this->generator->generateWhere($this->whereConditions, $this->where, $this->bindValues, $this->bindTypes);
        }

        $this->query = "";
        $this->combineQuery();

        // Verify if bind has same number as the number of question marks in the query
        // TODO: Implement this when stable
        //$this->verifyQuery();

        $connection = ConnectionManager::getConnection();
        $query = $connection->prepare($this->query);

        // Bind all values
        $idx = 1;
        foreach ($this->bindValues as $key => $value) {
            $query->bindValue($idx, $value, $this->bindTypes[$key]);
            $idx++;
        }

        // Execute
        $status = $query->execute();

        if ($status && $this->queryType === self::QUERY_INSERT) {
            return $connection->lastInsertId();
        }
        return $status;
    }


    /**
     * Only prepare the query SQL statement and return PDO Statement back.
     * WARNING, don't use this to fetch! Only for update/insert!
     *
     * @param string $sql
     *
     * @return \PDOStatement
     */
    public function prepare($sql)
    {
        $connection = ConnectionManager::getConnection();
        $statement = $connection->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_NUM);
        return $statement;
    }










    /**
     * Check if given operator is a valid operator.
     * @param string $operator
     * @return bool
     */
    private function validOperator($operator)
    {
        $valid = array("=", "!=", "LIKE", ">", "<", ">=", "<=", "IN", "<>", "IS NOT");
        return in_array($operator, $valid, true);
    }

    /**
     * Validate value for given operator
     * @param mixed $value
     * @param string $operator
     * @return bool
     */
    private function validValue($value, $operator)
    {
        if (! $this->validOperator($operator)) {
            return false; // @codeCoverageIgnore
        }

        if ($operator === "IN") {
            // Valid should be an array!
            return is_array($value);
        }
        if ($operator === "IS NOT") {
            return is_null($value);
        }

        return !is_array($value);
    }

    /**
     * Validate type of ordering columns
     * @param string $type
     * @return bool
     */
    private function validOrderType($type)
    {
        return strtolower($type) === 'asc' || strtolower($type) === 'desc';
    }


    /**
     * Combine Query
     */
    private function combineQuery()
    {
        if ($this->queryType === self::QUERY_SELECT) {
            $this->query = "SELECT $this->start FROM $this->table";

            if (! empty($this->where)) {
                $this->query .= " WHERE $this->where";
            }

            if (! empty($this->order)) {
                $this->query .= " ORDER BY $this->order";
            }

            if (! empty($this->limit)) {
                $this->query .= " LIMIT $this->limit";
            }
        }

        if ($this->queryType === self::QUERY_INSERT) {
            // The start will contain the column names () in order! Then the data will contain the value ()
            $this->query = "INSERT INTO $this->table $this->start VALUES $this->data ;";
        }

        if ($this->queryType === self::QUERY_UPDATE) {
            $this->query = "UPDATE $this->table SET $this->data";

            if (! empty($this->where)) {
                $this->query .= " WHERE $this->where";
            }
        }

        if ($this->queryType === self::QUERY_DELETE) {
            $this->query = "DELETE FROM $this->table WHERE $this->where";
        }
    }

    /**
     * Inject save state on fetched entities and return
     * @param Entity|Entity[] $result
     * @return Entity|Entity{}
     */
    private function injectState($result)
    {
        return EntityManager::getInstance()->afterFetch(true, $result);
    }
}