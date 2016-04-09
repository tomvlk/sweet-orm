<?php
/**
 * Validation Result
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Validator;

/**
 * Class ValidationResult
 * @package SweetORM\Structure\Validator
 */
class ValidationResult
{
    /**
     * Successfully validated
     * @var boolean
     */
    private $success;

    /**
     * Array with errors.
     * @var array
     */
    private $errors;

    /**
     * Message given for the result.
     * @var string
     */
    private $message;

    /**
     * ValidationResult constructor.
     * @param bool $success Successfully validated/parsed?
     * @param array $errors Optional errors.
     * @param string $message Optional message.
     */
    public function __construct($success, $errors = array(), $message = null)
    {
        $this->success = $success;
        $this->errors = $errors;
        $this->message = $message;
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


}