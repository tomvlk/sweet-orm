<?php
/**
 * Entity Structure
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure;

/**
 * Entity Database Structure
 * Indexed Annotations
 *
 * @package SweatORM\Structure
 */
class EntityStructure
{
    /**
     * Read Only! Class name of the Entity, including namespaces.
     *
     * @var string
     */
    public $name;

    /**
     * Read Only! Table name for entity.
     *
     * @var string
     */
    public $tableName;


    /**
     * Read Only! Table Annotation data.
     *
     * @var Table
     */
    public $table;

    /**
     * Read Only! Column data of table.
     *
     * @var Column[]
     */
    public $columns = array();

    /**
     * Read Only! Column Names.
     * @var string[]
     */
    public $columnNames = array();

    /**
     * Read Only! Column that is primary key.
     * @var Column
     */
    public $primaryColumn;

    /**
     * Read Only! Foreign Column Names
     * @var string[]
     */
    public $foreignColumnNames = array();

    /**
     * Read Only! Virtual Property names of declared relations
     * @var string[]
     */
    public $relationProperties = array();

    /**
     * Read Only! Relations to other Entities
     * @var Relation[]
     */
    public $relations = array();


}