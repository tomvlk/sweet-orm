<?php
/**
 * Validator Superclass.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Validator;
use SweetORM\Entity;
use SweetORM\Structure\EntityStructure;

/**
 * Abstract Class Validator.
 * @package SweetORM\Structure\Validator
 */
abstract class Validator
{
    /**
     * Entity Structure to validate against.
     * @var EntityStructure
     */
    protected $structure;

    public function __construct(EntityStructure $entityStructure)
    {
        $this->structure = $entityStructure;
    }

    /**
     * Test data on the entity.
     *
     * @param mixed $data Input data.
     * @param array $options Optional array with custom options.
     * @return ValidationResult Result of testing.
     * @throws \Exception Could throw exceptions too.
     */
    abstract public function test ($data, $options = array());

    /**
     * Create new entity with data given,
     * This will first invoke the validator, then parse and fill in a new entity.
     *
     * @param mixed $data Input data.
     * @param array $options Optional array with custom options.
     * @return Entity|ValidationResult Entity instance or when validation failed, ValidationResult instance.
     * @throws \Exception Could throw exceptions too.
     */
    abstract public function create ($data, $options = array());

    /**
     * Update entity with data. Will merge and override current data,
     * Except for the primary key(s).
     * This will first invoke the validator, then parse and fill in a new entity.
     *
     * @param Entity $entity Input entity.
     * @param mixed $data Input data.
     * @param array $options Optional array with custom options.
     * @return Entity|ValidationResult Entity instance or when validation failed, ValidationResult instance.
     * @throws \Exception Could throw exceptions too.
     */
    abstract public function fill (Entity &$entity, $data, $options = array());
}