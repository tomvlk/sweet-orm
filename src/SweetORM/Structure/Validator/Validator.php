<?php
/**
 * Validator Superclass.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Validator;
use SweetORM\Entity;
use SweetORM\Structure\Annotation\Column;
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

    /**
     * Data to test against.
     * @var mixed
     */
    protected $data;

    /**
     * Validator constructor.
     * @param EntityStructure $entityStructure
     * @param mixed $data Data already providing.
     */
    public function __construct(EntityStructure $entityStructure, $data)
    {
        $this->structure = $entityStructure;
        $this->data = $data;
    }

    /**
     * Test data on the entity.
     *.
     * @param array $options Optional array with custom options.
     * @return ValidationResult Result of testing.
     * @throws \Exception Could throw exceptions too.
     */
    abstract public function test ($options = array());

    /**
     * Create new entity with data given,
     * This will first invoke the validator, then parse and fill in a new entity.
     *
     * @param array $options Optional array with custom options.
     * @return Entity|ValidationResult Entity instance or when validation failed, ValidationResult instance.
     * @throws \Exception Could throw exceptions too.
     */
    abstract public function create ($options = array());

    /**
     * Update entity with data. Will merge and override current data,
     * Except for the primary key(s).
     * This will first invoke the validator, then parse and fill in a new entity.
     *
     * @param Entity $entity Input entity.
     * @param array $options Optional array with custom options.
     * @return Entity|ValidationResult Entity instance or when validation failed, ValidationResult instance.
     * @throws \Exception Could throw exceptions too.
     */
    abstract public function fill (Entity &$entity, $options = array());
}