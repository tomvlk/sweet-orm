<?php
/**
 * Entity Indexer
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Indexer;

use Doctrine\Common\Annotations\AnnotationReader;
use SweetORM\Exception\InvalidAnnotationException;
use SweetORM\Structure\Annotation\Entity;
use SweetORM\Structure\EntityStructure;

/**
 * Class EntityIndexer, Will be used to index the whole Entity with it's annotations
 *
 * @package SweetORM\Structure\Indexer
 */
class EntityIndexer
{
    /** @var array Indexer class names */
    private static $indexers = array(
        "TableIndexer",
        "ColumnIndexer",
        "RelationIndexer"
    );

    /**
     * @var \ReflectionClass Entity Class ReflectionClass
     */
    private $entityClass;

    /**
     * @var array Annotations of class itself
     */
    private $classAnnotations = array();

    /**
     * Entity Annotation Classs Instance
     * @var Entity|null
     */
    private $entityAnnotation;


    /**
     * @var EntityStructure structure
     */
    private $entity;

    /**
     * Entity Indexer
     * @param string|Entity $entityClassName
     *
     * @throws InvalidAnnotationException Invalid annotations used
     */
    public function __construct($entityClassName)
    {
        $this->entityClass = new \ReflectionClass($entityClassName);

        if (! $this->entityClass->isSubclassOf("\\SweetORM\\Entity")) {
            throw new \UnexpectedValueException("The className for getTable should be a class that is extending the SweetORM Entity class"); // @codeCoverageIgnore
        }

        $this->entity = new EntityStructure();
        $this->entity->name = $this->entityClass->getName();

        // Reader
        $reader = new AnnotationReader();

        $this->classAnnotations = $reader->getClassAnnotations($this->entityClass);
        $this->entityAnnotation = $reader->getClassAnnotation($this->entityClass, Entity::class);

        // Validate Entity annotation
        if ($this->entityAnnotation === null || ! $this->entityAnnotation instanceof Entity) {
            throw new InvalidAnnotationException("Entity '".$this->entityClass->getName()."' should use Annotations to use it! Please look at the documentation for help."); // @codeCoverageIgnore
        }

        // Run all the indexers
        foreach(self::$indexers as $indexerClass) {
            $indexerFullClass = "\\SweetORM\\Structure\\Indexer\\" . $indexerClass;

            /** @var Indexer $instance */
            $instance = new $indexerFullClass($this->entityClass, $reader);
            $instance->indexEntity($this->entity);
        }
    }

    /**
     * Get entity structure instance, indexed entity.
     *
     * @return EntityStructure
     */
    public function getEntity()
    {
        return $this->entity;
    }
}