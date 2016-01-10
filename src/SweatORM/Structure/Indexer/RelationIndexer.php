<?php
/**
 * Relation Indexer
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Indexer;

use Doctrine\Common\Annotations\AnnotationReader;
use SweatORM\Exception\InvalidAnnotationException;
use SweatORM\Structure\Column;
use SweatORM\Structure\EntityStructure;
use SweatORM\Structure\OneToMany;
use SweatORM\Structure\OneToOne;
use SweatORM\Structure\Relation;

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
     */
    private function oneToOne(&$structure, $property, $relation)
    {
        var_dump($relation);
    }

    /**
     * @param EntityStructure $structure
     * @param \ReflectionProperty $property
     * @param OneToMany|Relation $relation
     */
    private function oneToMany(&$structure, $property, $relation)
    {
        //var_dump($relation);
    }
}