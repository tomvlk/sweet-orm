<?php
/**
 * ManyToMany Solver
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Database\Solver;

use Doctrine\Common\Collections\ArrayCollection;
use SweetORM\Database\Solver;
use SweetORM\Entity;
use SweetORM\EntityManager;
use SweetORM\Structure\Annotation\JoinTable;

class ManyToMany extends Solver
{
    /**
     * Fetch target entity or entities.
     *
     * @param Entity $entity
     * @return mixed
     */
    public function solveFetch(Entity &$entity)
    {
        /** @var JoinTable $joinTable */
        $joinTable = $this->relation->join;

        // Get target entity structure
        $targetStructure = EntityManager::getInstance()->getEntityStructure($joinTable->targetEntityName);

        $query = "SELECT * FROM {$targetStructure->tableName} as target, {$joinTable->name} as couple WHERE target.{$joinTable->targetColumn->entityColumn} = couple.{$joinTable->targetColumn->name} AND couple.{$joinTable->column->name} = ?;";

        $bind = array($entity->{$joinTable->column->entityColumn});
        $results = EntityManager::query($this->relation->targetEntity)->custom($query, $bind);

        return $results;
    }

    /**
     * Solve when saving, this will only be called when changes made to the relation property!
     *
     * @param Entity $entity
     * @param ArrayCollection $value
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function solveSave(Entity &$entity, &$value)
    {
        // TODO: Implement solveSave() method.
    }
}