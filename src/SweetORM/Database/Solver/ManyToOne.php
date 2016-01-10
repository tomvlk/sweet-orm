<?php
/**
 * ManyToOne Solver
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Database\Solver;

use SweetORM\Database\Solver;
use SweetORM\Entity;

class ManyToOne extends Solver
{
    /**
     * Fetch target entity or entities.
     *
     * @param Entity $entity
     * @return mixed
     */
    public function solve(Entity &$entity)
    {
        // We can use the static ::get method on the entity
        $targetEntity = $this->relation->targetEntity;

        return call_user_func($targetEntity . '::get', $entity->{$this->relation->join->column});
    }
}