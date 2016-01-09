<?php
/**
 * Indexer Interface
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweatORM\Structure\Indexer;
use Doctrine\Common\Annotations\AnnotationReader;
use SweatORM\Structure\EntityStructure;

/**
 * Indexer
 *
 * @package SweatORM\Structure\Indexer
 */
interface Indexer
{
    /**
     * Start the indexer, hold the entity class
     * @param \ReflectionClass $entityClass
     * @param AnnotationReader $reader
     */
    public function __construct(\ReflectionClass $entityClass, AnnotationReader $reader);

    /**
     * Start indexing entity for the indexer specific content
     *
     * @param EntityStructure $structure Structure reference
     * @return mixed
     */
    public function indexEntity(&$structure);
}