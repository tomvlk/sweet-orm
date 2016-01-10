<?php
/**
 * Abstract Solver
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Database;


use SweetORM\Entity;
use SweetORM\EntityManager;
use SweetORM\Structure\Annotation\Relation;
use SweetORM\Structure\EntityStructure;

abstract class Solver
{
    /**
     * @var Relation
     */
    protected $relation;
    /**
     * @var EntityStructure
     */
    protected $structure;
    /**
     * @var EntityStructure
     */
    protected $targetStructure;
    /**
     * @var Query
     */
    protected $query;

    /**
     * Solver constructor.
     * @param Relation $relation
     * @param EntityStructure $structure
     * @param Query $query
     */
    public function __construct(&$relation, &$structure, &$query)
    {
        $this->relation = &$relation;
        $this->structure = &$structure;
        $this->query = &$query;

        // Determinate target structure
        $this->targetStructure = EntityManager::getInstance()->getEntityStructure($relation->targetEntity);
    }

    /**
     * Fetch target entity or entities.
     *
     * @param Entity $entity
     *
     * @return mixed
     */
    public abstract function solve(Entity &$entity);
}