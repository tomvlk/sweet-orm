<?php
/**
 * Column Indexer
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Indexer;

use Doctrine\Common\Annotations\AnnotationReader;
use SweetORM\Exception\InvalidAnnotationException;
use SweetORM\Structure\Annotation\Column;
use SweetORM\Structure\Annotation\Constraint;
use SweetORM\Structure\EntityStructure;

/**
 * Column Indexer
 *
 * @package SweetORM\Structure\Indexer
 */
class ColumnIndexer implements Indexer
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

            $column = $this->reader->getPropertyAnnotation($property, Column::class);

            if ($column !== null && $column instanceof Column) {
                // Add to the column stack.
                if ($column->name == null) {
                    $column->name = $property->getName();
                }

                if ($column->type == null) {
                    throw new InvalidAnnotationException("Entity '".$this->entityClass->getName()."' has property ('".$property->getName()."') with @Column but no type is given!"); // @codeCoverageIgnore
                }

                // Set property name
                $column->propertyName = $property->getName();

                // Fetch constraint if provided
                $constraint = $this->reader->getPropertyAnnotation($property, Constraint::class);
                if ($constraint !== null && $constraint instanceof Constraint) {
                    $column->constraint = $constraint;
                }

                // Save column
                $structure->columnNames[] = $column->name;
                $structure->columns[] = $column;

                if ($column->primary) {
                    $structure->primaryColumn = $column;
                }
            }
        }

        // If no primary key is given we will throw an exception
        if ($structure->primaryColumn == null) {
            throw new InvalidAnnotationException("Entity '".$this->entityClass->getName()."' has no primary key column defined!"); // @codeCoverageIgnore
        }
    }
}