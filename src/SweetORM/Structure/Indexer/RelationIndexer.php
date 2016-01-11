<?php
/**
 * Relation Indexer
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Indexer;

use Doctrine\Common\Annotations\AnnotationReader;
use SweetORM\Entity;
use SweetORM\EntityManager;
use SweetORM\Exception\InvalidAnnotationException;
use SweetORM\Exception\RelationException;
use SweetORM\Structure\Annotation\JoinColumn;
use SweetORM\Structure\Annotation\JoinTable;
use SweetORM\Structure\Annotation\ManyToMany;
use SweetORM\Structure\Annotation\ManyToOne;
use SweetORM\Structure\EntityStructure;
use SweetORM\Structure\Annotation\Join;
use SweetORM\Structure\Annotation\OneToMany;
use SweetORM\Structure\Annotation\OneToOne;
use SweetORM\Structure\Annotation\Relation;

/**
 * Relation Indexer
 *
 * @package SweetORM\Structure\Indexer
 */
class RelationIndexer implements Indexer
{
    /**
     * @var \ReflectionClass
     */
    private $entityClass;
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * Start the indexer, hold the entity class
     * @param \ReflectionClass $entityClass
     * @param AnnotationReader $reader
     */
    public function __construct(\ReflectionClass $entityClass, AnnotationReader $reader)
    {
        $this->entityClass = $entityClass;
        $this->reader = $reader;
    }

    /**
     * Start indexing entity for the indexer specific content
     *
     * @param EntityStructure $structure Structure reference
     * @return mixed
     * @throws InvalidAnnotationException
     */
    public function indexEntity(&$structure)
    {
        $properties = $this->entityClass->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            /** @var \ReflectionProperty $property */

            $relation = $this->reader->getPropertyAnnotation($property, Relation::class);

            if ($relation !== null && $relation instanceof Relation) {
                $relationType = get_class($relation);

                if ($relationType === OneToOne::class || $relationType === ManyToOne::class) {
                    $this->oneToOne($structure, $property, $relation);
                }
                if ($relationType === OneToMany::class) {
                    $this->oneToMany($structure, $property, $relation);
                }
                if ($relationType === ManyToMany::class) {
                    $this->manyToMany($structure, $property, $relation);
                }
            }
        }
    }

    /**
     * @param EntityStructure $structure
     * @param \ReflectionProperty $property
     * @param OneToOne|Relation $relation
     *
     * @throws RelationException Class not correct, no target property found or not extending Entity.
     * @throws \ReflectionException Class not found
     */
    private function oneToOne(&$structure, $property, $relation)
    {
        $from = $structure->name;
        $to = $relation->targetEntity;

        $reflection = null;
        try {
            $reflection = new \ReflectionClass($to);
        }catch(\Exception $e) {
            // Ignore, we will throw error on the next if.
        }
        if ($reflection === null || ! $reflection->isSubclassOf(Entity::class)) {
            throw new RelationException("The target entity of your relation on the entity '".$from."' and property '".$property->getName()."' has an unknown target Entity!"); // @codeCoverageIgnore
        }

        // Get join and set the join into the relation
        $join = $this->getJoin($property);
        $relation->join = $join;

        // Add declaration to the structure
        $structure->relationProperties[] = $property->getName();
        $structure->foreignColumnNames[] = $join->column;
        $structure->relations[$property->getName()] = $relation;
    }

    /**
     * @param EntityStructure $structure
     * @param \ReflectionProperty $property
     * @param OneToMany|Relation $relation
     *
     * @throws RelationException Class not correct, no target property found or not extending Entity.
     * @throws \ReflectionException Class not found
     */
    private function oneToMany(&$structure, $property, $relation)
    {
        $from = $structure->name;
        $to = $relation->targetEntity;

        $reflection = null;
        try {
            $reflection = new \ReflectionClass($to);
        }catch(\Exception $e) {
            // Ignore, we will throw error on the next if.
        }
        if ($reflection === null || ! $reflection->isSubclassOf(Entity::class)) {
            throw new RelationException("The target entity of your relation on the entity '".$from."' and property '".$property->getName()."' has an unknown target Entity!"); // @codeCoverageIgnore
        }

        $join = $this->getJoin($property);
        $relation->join = $join;

        // Add declaration to the structure
        $structure->relationProperties[] = $property->getName();
        $structure->foreignColumnNames[] = $join->column;
        $structure->relations[$property->getName()] = $relation;
    }

    /**
     * @param EntityStructure $structure
     * @param \ReflectionProperty $property
     * @param ManyToMany|Relation $relation
     *
     * @throws RelationException Class not correct, no target property found or not extending Entity.
     * @throws \ReflectionException Class not found
     */
    private function manyToMany(&$structure, $property, $relation)
    {
        $from = $structure->name;
        $to = $relation->targetEntity;

        $reflection = null;
        try {
            $reflection = new \ReflectionClass($to);
        }catch(\Exception $e) { // @codeCoverageIgnore
            // Ignore, we will throw error on the next if. // @codeCoverageIgnore
        }
        if ($reflection === null || ! $reflection->isSubclassOf(Entity::class)) {
            throw new RelationException("The target entity of your relation on the entity '".$from."' and property '".$property->getName()."' has an unknown target Entity!"); // @codeCoverageIgnore
        }

        /** @var JoinTable $join */
        $join = $this->getJoin($property, JoinTable::class);
        $join->sourceEntityName = $from;
        $join->targetEntityName = $to;

        $relation->join = $join;

        // Register the join table
        EntityManager::getInstance()->registerJoinTable($join);

        // Add declaration to the structure
        $structure->relationProperties[] = $property->getName();
        $structure->foreignColumnNames[] = $join->column;
        $structure->relations[$property->getName()] = $relation;
    }

    /**
     * Get Join(table)
     *
     * @param \ReflectionProperty $property
     * @param string $type Class of join type
     * @param bool $exception
     *
     * @return Join|JoinTable
     *
     * @throws RelationException
     */
    private function getJoin(\ReflectionProperty $property, $type = Join::class, $exception = true)
    {
        /** @var Join|JoinTable $join */
        $join = $this->reader->getPropertyAnnotation($property, $type);
        if ($exception && ($join === null || ! $join instanceof $type)) {
            throw new RelationException("Relation in '".$property->getDeclaringClass()->getName()."' -> '".$property->getName()."' should have @$type annotation!"); // @codeCoverageIgnore
        }
        if ($exception && $type === Join::class && ($join->column === null || $join->targetColumn === null)) {
            throw new RelationException("Join in '".$property->getDeclaringClass()->getName()."' -> '".$property->getName()."' should have the local column and targetColumn filled in!"); // @codeCoverageIgnore
        }
        if ($exception && $type === JoinTable::class && ($join->name == "" || ! $join->column instanceof JoinColumn || ! $join->targetColumn instanceof JoinColumn)) {
            throw new RelationException("JoinTable in '".$property->getDeclaringClass()->getName()."' -> '".$property->getName()."' should have a table name and 2 join columns in column and targetColumn!"); // @codeCoverageIgnore
        }
        return $join;
    }
}