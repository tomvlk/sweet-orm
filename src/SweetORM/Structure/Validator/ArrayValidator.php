<?php
/**
 * ArrayValidator, Validates array against entity.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Validator;

use SweetORM\Entity;
use SweetORM\Structure\Annotation\Column;

/**
 * Array Validator and filler.
 *
 * @package SweetORM\Structure\Validator
 */
class ArrayValidator extends Validator
{

    /**
     * Test data on the entity.
     *
     * @param array $options Optional array with custom options.
     * @return ValidationResult Result of testing.
     * @throws \Exception Could throw exceptions too.
     */
    public function test($options = array())
    {
        if (! is_array($this->data)) {
            return new ValidationResult(false, array('No valid data given!'), 'No valid data type given!');
        }
        $columns = $this->structure->columns;

        $errors = array();
        $success = true;

        foreach ($columns as $col) { /** @var Column $col */

            if (isset($this->data[$col->name])) {
                if (! $col->null && $this->data[$col->name] === null) {
                    $success = false;
                    $errors[] = 'Column \'' . $col->name . '\' could not be empty!';
                } else {
                    // Check types.
                    $typeFailed = false;

                    switch ($col->type) {
                        case 'string'||'text':
                            $typeFailed = is_string($this->data[$col->name]);
                            break;
                        case 'integer':
                            $typeFailed = is_integer($this->data[$col->name]);
                            break;
                        case 'float':
                            $typeFailed = is_float($this->data[$col->name]);
                            break;
                        case 'double':
                            $typeFailed = is_double($this->data[$col->name]);
                            break;
                        case 'date':
                            $typeFailed = is_string($this->data[$col->name]) || is_int($this->data[$col->name]);
                            // Could be string or integer format. If not blocked, also check date itself.
                            if (! isset($options['datevalidation']) || ! $options['datevalidation']) {
                                $typeFailed = strtotime($this->data[$col->name]) !== false;
                            }
                            break;
                        default:
                            $typeFailed = false;
                            break;
                    }

                    if (! $typeFailed) {
                        $success = false;
                        $errors[] = 'Given data for column \'' . $col->name . '\' has a wrong type. Must be \'' . $col->type . '\'';
                    }
                }
            } elseif (! $col->null && ! $col->primary && ! $col->default) {
                $success = false;
                $errors[] = 'Column \'' . $col->name . '\' could not be empty!';
            }// else success!
        }
        return new ValidationResult($success, $errors, $success ? null : 'Errors happend, check the errors array!');
    }

    /**
     * Create new entity with data given,
     * This will first invoke the validator, then parse and fill in a new entity.
     *
     * @param array $options Optional array with custom options.
     * @return Entity|ValidationResult Entity instance or when validation failed, ValidationResult instance.
     * @throws \Exception Could throw exceptions too.
     */
    public function create($options = array())
    {
        // TODO: Implement create() method.
    }

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
    public function fill(Entity &$entity, $options = array())
    {
        // TODO: Implement fill() method.
    }
}