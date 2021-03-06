<?php
/**
 * Query Generator Description
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Database;

use SweetORM\Structure\Annotation\Column;

class QueryGenerator
{

    /** @var bool */
    private $verify = true;

    /**
     * QueryGenerator constructor.
     */
    public function __construct()
    {
    }

    /**
     * Set if we are going to verify the columns
     *
     * @param bool $verify
     * @codeCoverageIgnore
     */
    public function setVerify($verify)
    {
        $this->verify = $verify;
    }


    /**
     * Generate Joins
     *
     * @param array $joins
     * @param string $join
     */
    public function generateJoin($joins, &$join)
    {
        $join = "";

        if (count($joins) == 0) return;

        foreach ($joins as $joinEntry) {
            $table = $joinEntry['table'];
            $on = $joinEntry['on'];
            $alias = $joinEntry['alias'];
            $type = $joinEntry['type'];

            $join .= "$type $table ";
            if ($alias !== null) $join .= " $alias ";
            $join .= " ON $on ";

            $join .= " ";
        }
    }


    /**
     * Generate Where clause
     *
     * @param array $conditions
     * @param string $where Reference of where string
     * @param array $values Reference of binding values to append
     * @param array $types Reference of binding value types to append
     */
    public function generateWhere($conditions, &$where, &$values, &$types)
    {
        $where = "";

        // When empty where, no conditions, return and set where empty.
        if (count($conditions) == 0) {
            return;
        }

        $idx = 0;
        $max = count($conditions);

        foreach ($conditions as $criteria) {
            $column = $criteria['column'];
            $operator = $criteria['operator'];
            $value = $criteria['value'];

            // Prepare where clause
            $where .= "$column ";

            // Check our operator
            if ($operator === "IN") {
                // Prepare and loop
                $where .= "IN (";

                $subIdx = 0;
                $subMax = count($value);
                foreach ($value as $subValue) {

                    $where .= "?";
                    $values[] = $this->prepareData($subValue);
                    $types[] = $this->determinateType($subValue);

                    if (($subIdx+1) < $subMax) {
                        $where .= ",";
                    }
                    $subIdx++;
                }
                $where .= ")";
            } else {
                // Will add a normal operator
                $where .= "$operator ";

                $where .= "?";
                $values[] = $this->prepareData($value);
                $types[] = $this->determinateType($value);
            }

            // Adding AND if not last
            if (($idx + 1) < $max) {
                $where .= " AND ";
            }
            $idx++;
        }
    }

    /**
     * Generate Order By
     *
     * @param string $sortBy Column name
     * @param string $sortOrder Type of order
     * @param string $order Reference to Order result
     */
    public function generateOrder($sortBy, $sortOrder, &$order)
    {
        if (! empty($sortBy)) {
            if (empty($sortOrder)) { // @codeCoverageIgnore
                $sortOrder = "ASC"; // @codeCoverageIgnore
            } // @codeCoverageIgnore

            $order = "$sortBy $sortOrder";
        }
    }


    /**
     * Generate Limit part
     *
     * @param int $limitCount
     * @param int $limitOffset
     * @param string $limit Reference for the resulting query part
     */
    public function generateLimit($limitCount, $limitOffset, &$limit)
    {
        $limit = "";

        if (empty($limitCount)) {
            return;
        }

        $limit = "$limitCount";

        if (! empty($limitOffset)) {
            $limit .= ",$limitOffset";
        }
    }


    /**
     * Generate Insert parts
     *
     * @param Column[] $columnOrder
     * @param array $changeData
     * @param string $start Reference: COLUMN definition part
     * @param string $data Reference: VALUES () part
     * @param array $values Reference: binding values
     * @param array $types Reference: binding types
     */
    public function generateInsert($columnOrder, $changeData, &$start, &$data, &$values, &$types)
    {
        // Prepare
        $start = "(";
        $data = "(";

        // Generate the column definition
        $idx = 0;
        $max = count($columnOrder);
        foreach ($columnOrder as $column) {
            // Get value for column
            $value = null;
            if (isset($changeData[$column->name])) {
                $value = $changeData[$column->name];
            }

            // Add to the definition line
            $start .= "`".$column->name."`";

            // Add to the values line
            $data .= "?";
            $values[] = $this->prepareData($changeData[$column->name]);
            $types[] = $this->determinateType($changeData[$column->name]);

            // Adding ,
            if (($idx + 1) < $max) {
                $start .= ",";
                $data .= ",";
            }

            $idx++;
        }

        // Finish start and data
        $start .= ")";
        $data .= ")";
    }

    /**
     * Generate Update Lines
     *
     * @param array $changeData
     * @param string $data Reference: Data (set) line
     * @param array $values Reference: Bind values
     * @param array $types Reference: Bind types
     */
    public function generateUpdate($changeData, &$data, &$values, &$types)
    {
        // Prepare
        $data = "";

        // Generate the column definition
        $idx = 0;
        $max = count($changeData);
        foreach ($changeData as $column => $value) {
            // Prepare set part
            $data .= "$column = ?";
            $values[] = $this->prepareData($value);
            $types[] = $this->determinateType($value);

            // Adding ,
            if (($idx + 1) < $max) {
                $data .= ",";
            }

            $idx++;
        }
    }






    /**
     * Detect PDO type of value
     *
     * @param mixed $value
     * @return int
     */
    private function determinateType($value)
    {
        if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        }elseif(is_int($value)) {
            return \PDO::PARAM_INT;
        }elseif($value === null) {
            return \PDO::PARAM_NULL;
        }
        return \PDO::PARAM_STR;
    }

    /**
     * Will parse any date or other data that needs manipulation before inserting/updating/selecting.
     *
     * @param mixed $value
     * @return string
     */
    private function prepareData($value)
    {
        if ($value instanceof \DateTime)
            return $value->format('c');

        return $value;
    }
}
