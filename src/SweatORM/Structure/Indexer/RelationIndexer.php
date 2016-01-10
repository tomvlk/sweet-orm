<?php
/**
 * Relation Indexer
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Indexer;

use Doctrine\Common\Annotations\AnnotationReader;
use SweatORM\Entity;
use SweatORM\Exception\InvalidAnnotationException;
use SweatORM\Exception\RelationException;
use SweatORM\Structure\EntityStructure;
use SweatORM\Structure\Annotation\Join;
use SweatORM\Structure\Annotation\OneToMany;
use SweatORM\Structure\Annotation\OneToOne;
use SweatORM\Structure\Annotation\Relation;

/**
 * Relation Indexer
 *
 * @package SweatORM\Structure\Indexer
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
                switch(get_class($relation)) {
                    case OneToOne::class:
                        $this->oneToOne($structure, $property, $relation);
                        break;
                    case OneToMany::class:
                        $this->oneToMany($structure, $property, $relation);
                        break;
                }
            }
        }
    }


    /**
     * @param EntityStructure $structure
     * @param \ReflectionProperty $property
     * @param OneToOne|Relation $relation
     * @throws RelationException Class not correct, no target property found or not extending Entity.
     * @throws \ReflectionException Class not found
     */
    private function oneToOne(&$structure, $property, $relation)
    {
        $from = $structure->name;
        $to = $relation->targetEntity;

        $reflection = new \ReflectionClass($to);
        if (! $reflection->isSubclassOf(Entity::class)) {
            throw new RelationException("The target entity of your relation on the entity '".$from."' and property '".$property->getName()."' has an unknown target Entity!");
        }

        // Get join and set the join into the relation
        $join = $this->getJoin($property);
        $relation->join = $join;

        // Add declaration to the structure
        $structure->relationProperties[] = $property->getName();
        $structure->foreignColumnNames[] = $join->column;
        $structure->relations[] = $relation;
    }

    /**
     * @param EntityStructure $structure
     * @param \ReflectionProperty $property
     * @param OneToMany|Relation $relation
     * @throws RelationException Class not correct, no target property found or not extending Entity.
     * @throws \ReflectionException Class not found
     */
    private function oneToMany(&$structure, $property, $relation)
    {
        $from = $structure->name;
        $to = $relation->targetEntity;

        $reflection = new \ReflectionClass($to);
        if (! $reflection->isSubclassOf(Entity::class)) {
            throw new RelationException("The target entity of your relation on the entity '".$from."' and property '".$property->getName()."' has an unknown target Entity!");
        }

        $join = $this->getJoin($property);
        // TODO: One to many.

    }

    /**
     * Get Join
     *
     * @param \ReflectionProperty $property
     * @param bool $exception
     * @return Join
     * @throws RelationException
     */
    private function getJoin(\ReflectionProperty $property, $exception = true)
    {
        $join = $this->reader->getPropertyAnnotation($property, Join::class);
        if ($exception && ($join === null || ! $join instanceof Join)) {
            throw new RelationException("Relation in '".$property->getDeclaringClass()."' -> '".$property->getName()."' should have @Join annotation!");
        }
        if ($exception && ($join->column === null || $join->targetColumn === null)) {
            throw new RelationException("Join in '".$property->getDeclaringClass()."' -> '".$property->getName()."' should have the local column and targetColumn filled in!");
        }
        return $join;
    }
}