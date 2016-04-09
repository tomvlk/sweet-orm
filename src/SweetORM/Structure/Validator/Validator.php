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

    /**
     * @param Column $column
     * @param mixed $value Value to test, null on empty or not set in input.
     * @param array $options Options for validating.
     * @return true|string True on success, string with error on failure.
     */
    protected function validateColumn (Column $column, $value = null, array $options)
    {
        if ($value === null && $column->null) {
            return true; // Allowed to be null!
        }
        if ($value === null && $column->primary && $column->autoIncrement) {
            return true; // Auto Increment on PK.
        }
        if ($value === null && $column->default) {
            return true; // Has Default value.
        }
        if ($value === null) {
            return 'Column \'' . $column->name . '\' cannot be empty or null!';
        }

        // Type validation
        $typeValid = null;
        switch ($column->type) {
            case 'string':
            case 'text':
                $typeValid = is_string($value);
                break;
            case 'integer':
                $typeValid = is_int($value);
                break;
            case 'bool':
                $typeValid = is_bool($value);
                break;
            case 'float':
                $typeValid = is_float($value);
                break;
            case 'double':
                $typeValid = is_double($value);
                break;
            case 'date':
                $typeValid = is_string($value) || is_int($value);
                // Could be string or integer format. If not blocked, also check date itself.
                if (is_string($value) && (! isset($options['datevalidation']) || ! $options['datevalidation'])) {
                    $typeValid = strtotime($value) !== false;
                }
                break;
            default:
                $typeValid = false;
                break;
        }
        if ($typeValid === false) {
            return 'Given data for column \'' . $column->name . '\' has a wrong type. Must be \'' . $column->type . '\' and \'' . gettype($value) . '\' is given!';
        }

        // Validate constraints if they are given
        if ($column->constraint === null) {
            return true; // Skip if there are no constraints.
        }

        // Validate constraints
        $constraintValid = $column->constraint->valid($value);
        if ($constraintValid === true) {
            return true;
        }
        return 'Constraints failed for column \'' . $column->name . '\': ' . implode(', ', $constraintValid);
    }

    /**
     * Validate and (if successful) enter the value to the entity instance.
     *
     * @param Entity $entity Entity Instance
     * @param Column $column Column annotation data.
     * @param mixed $value Value.
     * @param boolean $validation Validate before filling. Enabled by default, disable when already validated.
     * @param array $options Options.
     *
     * @return true|array True on success, array with strings (failed validation).
     */
    protected function fillColumn (Entity $entity, Column $column, $value, $validation = true, $options = array())
    {
        if ($validation) {
            // @codeCoverageIgnoreStart
            $valid = $this->validateColumn($column, $value, $options);
            if ($valid === true) {
                $entity->{$column->propertyName} = $value;
                return true;
            }
            return $valid;
            // @codeCoverageIgnoreEnd
        }
        $entity->{$column->propertyName} = $value;
        return true;
    }
}
