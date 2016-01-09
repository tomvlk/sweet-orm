<?php
/**
 * Table Indexer
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Indexer;


use Doctrine\Common\Annotations\AnnotationReader;
use SweatORM\Exception\InvalidAnnotationException;
use SweatORM\Structure\EntityStructure;
use SweatORM\Structure\Table;

class TableIndexer implements Indexer
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
        $table = $this->reader->getClassAnnotation($this->entityClass, Table::class);

        if (! $table instanceof Table) {
            throw new InvalidAnnotationException("Entity '".$this->entityClass->getName()."' has no Table entity!"); // @codeCoverageIgnore
        }

        if ($table->name == null) {
            throw new InvalidAnnotationException("Entity '".$this->entityClass->getName()."' is required to have the name parameter in the Table annotation."); // @codeCoverageIgnore
        }

        $structure->table = $table;
        $structure->tableName = $table->name;
    }
}