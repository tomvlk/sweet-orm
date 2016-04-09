<?php
/**
 * ValidationManager
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure;


use SweetORM\Structure\Validator\ArrayValidator;
use SweetORM\Structure\Validator\Validator;

/**
 * Validation Manager
 * @package SweetORM\Structure
 */
class ValidationManager
{

    /**
     * Get validator matching for the given data type.
     *
     * @param EntityStructure $entityStructure
     * @param mixed $data
     * @return Validator|false
     * @codeCoverageIgnore ignore due to the coverage issue in switches.
     */
    public static function validator (EntityStructure $entityStructure, $data)
    {
        switch (gettype($data)) {
            case 'array':
                return new ArrayValidator($entityStructure, $data);
            default:
                return false;
        }
    }
}