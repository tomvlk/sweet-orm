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
use SweetORM\Exception\RelationException;
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
    public function solveSave(Entity &$entity, &$value) // TODO: Refactor Query builder to be able to use it more often.
    {
        // We need to sync our collection with the jointable inbetween us.
        // We can have some deleted ones in our collection, but we are not sure.
        /** @var JoinTable $joinTable */
        $joinTable = $this->relation->join; // Table to sync it to.

        // First we need to verify if we got an ArrayCollection here
        if (! $value instanceof ArrayCollection) {
            throw new RelationException("Value given in your '".$this->structure->name."' entity relation (many to many) should always be an ArrayCollection instance!");
        }

        // Prepare to get id's from our entities!
        $currentId = $entity->_id;
        $insertIds = array();

        $ourColumn = $joinTable->column->name;
        $targetColumn = $joinTable->targetColumn->name;

        // Check if we only have an empty collection, then only delete from the joinTable
        if ($value->count() === 0) {
            // Only clear table contents (with join params)
            $delete = EntityManager::query($entity, false)
                ->delete($joinTable->name)
                ->where($ourColumn, $currentId)
                ->apply();

            if ($delete === false) {
                throw new RelationException("We can not solve the Many to Many relation save! Please verify your 
                structure in PHP classes and the database for the join table. Delete problems (all delete)!");
            }

            return true; // Make sure we stop here, no inserts needed
        }

        // Parse all items in the collection, prepare the jointable inserts.
        foreach ($value as $item) {
            if (is_int($item)) {
                // The user already gave us an ID only! Add it anyway
                $insertIds[] = $item;
                continue;
            }

            if (! $item instanceof Entity) {
                throw new RelationException("Values given in your '".$this->structure->name."' entity relation 
                (many to many) should always be an ArrayCollection with inside ID's or Entities!");
            }

            if ($item->_id === null) {
                throw new RelationException("Entities given inside of your ArrayCollection for relations 
                (entity: '".$this->structure->name."') should always be saved or fetched, 
                current entity not saved/fetched!");
            }

            $insertIds[] = $item->_id;
        }

        // We have enough information, we need to sync the db with our ids.
        // Delete all current entries with our current id in it.
        $delete = EntityManager::query($entity, false)
            ->delete($joinTable->name)
            ->where($ourColumn, $currentId)
            ->apply(); // This could be a bit better with syncing, instead of just replacing everything (TODO).
        if ($delete === false) {
            throw new RelationException("We can not solve the Many to Many relation save! 
            Please verify your structure in PHP classes and the database for the join table. Delete problems!");
        }

        $sql = "INSERT INTO $joinTable->name (`{$ourColumn}`, `{$targetColumn}`) VALUES ";
        $idx = 0;
        $max = count($insertIds);
        foreach ($insertIds as $rowNum => $rowId) {
            $sql .= " (:ourid, :target_$rowNum)";
            if (($idx+1) < $max) {
                $sql .= ", ";
            }
            $idx++;
        }
        $statement = EntityManager::query($entity, false)->prepare($sql);
        $statement->bindValue(":ourid", $currentId);
        foreach ($insertIds as $num => $rowId) {
            $statement->bindValue(":target_$num", $rowId);
        }

        $insert = $statement->execute();

        if ($insert === false) {
            throw new RelationException("We can not solve the Many to Many relation save! Please verify your structure in PHP classes and the database for the join table. Insert problems! " . implode(' - ', $statement->errorInfo()));
        }

        return true;
    }
}
