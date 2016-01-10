<?php
/**
 * Query Builder for ORM
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Database;
use SweatORM\ConnectionManager;
use SweatORM\Entity;
use SweatORM\EntityManager;
use SweatORM\Exception\ORMException;
use SweatORM\Exception\QueryException;
use SweatORM\Structure\EntityStructure;

/**
 * Query Building on Entities
 *
 * @package SweatORM\Database
 */
class Query
{
    const QUERY_SELECT = 1;
    const QUERY_INSERT = 2;
    const QUERY_UPDATE = 3;

    /**
     * @var string
     */
    private $class;

    /**
     * @var EntityStructure
     */
    private $structure;

    /**
     * Hold last exception (chain)
     * @var null|\Exception
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
    private $where = "";
    private $order = "";
    private $limit = "";


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

    /* ===== Storage for Binding ===== */
    private $bindValues = array();
    private $bindTypes = array();


    /**
     * Query Builder constructor.
     *
     * @param string|Entity $entityClass
     * @param bool $autoSelect Automaticly do the select and from based on the entity.
     * @throws ORMException
     */
    public function __construct($entityClass, $autoSelect = true)
    {
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
    public function into($table)
    {
        $this->table = $table;
        $this->queryType = self::QUERY_INSERT;
        return $this;
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
            if (! in_array($column, $columnNames)) {
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
     *
     * @return Entity[]|false Entities as successful result or false on not found.
     * @throws \Exception|null
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
     * @throws \Exception|null
     */
    public function one()
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->fetch(false);
    }


    /**
     * @param $multi
     * @return array|mixed
     */
    private function fetch($multi)
    {
        // Let the generator do his work now,
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

    /**
     * Inject save state on fetched entities and return
     * @param Entity|Entity[] $result
     * @return Entity|Entity{}
     */
    private function injectState($result)
    {
        if (! is_array($result)) {
            if ($result instanceof Entity) {
                $result->_saved = true;
            }
            return $result;
        }

        foreach ($result as $entity) {
            if ($entity instanceof Entity) {
                $entity->_saved = true;
            }
        }
        return $result;
    }
}