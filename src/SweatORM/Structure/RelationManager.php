<?php
/**
 * Relation Manager and solver
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure;
use SweatORM\Database\Query;
use SweatORM\Database\Solver;
use SweatORM\Entity;
use SweatORM\EntityManager;
use SweatORM\Exception\RelationException;
use SweatORM\Structure\Annotation\ManyToOne;
use SweatORM\Structure\Annotation\OneToOne;
use SweatORM\Structure\Annotation\Relation;

/**
 * Relation Manager and Solver
 *
 * @package SweatORM\Structure
 */
class RelationManager
{
    /** @var EntityStructure $structure */
    private $structure;

    /** @var array */
    private static $lazy = array();

    /**
     * @param Entity $entity
     * @param EntityStructure $structure Reference!
     * @return RelationManager
     */
    public static function with(&$entity, &$structure = null)
    {
        if ($structure === null) {
            $structure = EntityManager::getInstance()->getEntityStructure($entity);
        }

        return new RelationManager($entity, $structure);
    }


    /**
     * RelationManager constructor.
     * @param Entity $entity
     * @param EntityStructure $structure Reference!
     */
    public function __construct(&$entity, &$structure)
    {
        $this->entity = $entity;
        $this->structure = $structure;

        if (! isset(self::$lazy[get_class($entity)])) {
            self::$lazy[get_class($entity)] = array();
        }
    }

    /**
     * Clear Lazy cache
     * @codeCoverageIgnore
     */
    public static function clearCache()
    {
        self::$lazy = array();
    }

    /**
     * Solve relation
     *
     * @param string $virtualProperty
     * @param bool $cacheOnly Only from cache
     * @return mixed
     *
     * @throws RelationException
     * @throws \Exception
     */
    public function fetch($virtualProperty, $cacheOnly = false)
    {
        // Check for existing of the relation property
        if (! in_array($virtualProperty, $this->structure->relationProperties) || ! isset($this->structure->relations[$virtualProperty])) {
            throw new RelationException("Relation not defined!"); // @codeCoverageIgnore
        }

        // Make cache array if needed, for lazy loading
        if (! isset(self::$lazy[get_class($this->entity)][$virtualProperty])) {
            self::$lazy[get_class($this->entity)][$virtualProperty] = array();
        }

        /** @var Relation $relation */
        $relation = $this->structure->relations[$virtualProperty];
        if (! $relation instanceof Relation) {
            throw new RelationException("Relation indexing failed, something is really wrong, please report! Fetch proprty no instance of relation!"); // @codeCoverageIgnore
        }

        if (! isset($this->entity->{$relation->join->column})) {
            throw new \Exception("Property is not set at entity '".get_class($this->entity)."' when trying to solve relationship fetching."); // @codeCoverageIgnore
        }

        // Check if we have it in our cache
        $search = $this->entity->{$relation->join->column};
        if (isset( self::$lazy[get_class($this->entity)] [$virtualProperty] [$search] )) {
            return self::$lazy[get_class($this->entity)] [$virtualProperty] [$search];
        }

        // If only from cache then return null, as it isn't in the cache right now!
        if ($cacheOnly) {
            return null; // @codeCoverageIgnore
        }

        $solverName = join('', array_slice(explode("\\", get_class($relation)), -1));
        $class = "\\SweatORM\\Database\\Solver\\" . $solverName;

        /** @var Solver $solver */
        $solver = new $class($relation, $this->structure, new Query($relation->targetEntity, false));

        if (! $solver instanceof Solver) {
            throw new \Exception("Solver not found for relation '".get_class($relation)."'!"); // @codeCoverageIgnore
        }

        self::$lazy[get_class($this->entity)] [$virtualProperty] [$search] = $solver->solve($this->entity);
        return self::$lazy[get_class($this->entity)] [$virtualProperty] [$search];
    }


    /**
     * Set a new relationship value.
     *
     * @param string $virtualProperty
     * @param Entity|null $relationEntity
     *
     * @throws RelationException
     * @throws \Exception
     */
    public function set($virtualProperty, $relationEntity)
    {
        // Check for existing of the relation property
        if (! in_array($virtualProperty, $this->structure->relationProperties) || ! isset($this->structure->relations[$virtualProperty])) {
            throw new RelationException("Relation not defined!"); // @codeCoverageIgnore
        }

        // Make cache array if needed, for lazy loading
        if (! isset(self::$lazy[get_class($this->entity)][$virtualProperty])) {
            self::$lazy[get_class($this->entity)][$virtualProperty] = array();
        }

        /** @var Relation $relation */
        $relation = $this->structure->relations[$virtualProperty];
        if (! $relation instanceof Relation) {
            throw new RelationException("Relation indexing failed, something is really wrong, please report! Fetch proprty no instance of relation!"); // @codeCoverageIgnore
        }

        // Can only set OneToOne and ManyToOne
        if (! $relation instanceof OneToOne && ! $relation instanceof ManyToOne) {
            throw new RelationException("Only relations OneToOne and ManyToOne could be set!");
        }

        // If NULL then set null into the entity id column (fk)
        if ($relationEntity === null) {
            // Set null
            $this->entity->{$relation->join->column} = null;
            return;
        }

        // Get target structure
        $targetStructure = EntityManager::getInstance()->getEntityStructure($relationEntity);

        // Check if relationEntity is saved, if not throw exception!
        if (! $relationEntity->_saved) {
            throw new RelationException("Save the relationship entity first!");
        }

        // Set the id in the from entity
        $id = $relationEntity->{$targetStructure->primaryColumn->propertyName};
        $this->entity->{$relation->join->column} = $id;

        // Set the cache
        self::$lazy[get_class($this->entity)] [$virtualProperty] [$id] = $relationEntity;
    }
}