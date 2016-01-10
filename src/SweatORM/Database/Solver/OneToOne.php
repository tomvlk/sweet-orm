<?php
/**
 * OneToOne Solver
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Database\Solver;

use SweatORM\Database\Solver;
use SweatORM\Entity;

class OneToOne extends Solver
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