<?php
/**
 * Constraint Annotation, used for validating.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */

namespace SweetORM\Structure\Annotation;

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;
use Doctrine\Common\Annotations\Annotation\Enum;
use SweetORM\Structure\BaseAnnotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Constraint implements BaseAnnotation
{
    /**
     * Minimum characters.
     * @var int
     */
    public $minLength;

    /**
     * Maximum characters.
     * @var int
     */
    public $maxLength;

    /**
     * Minimum value (only for numeric values).
     * @var mixed
     */
    public $minValue;

    /**
     * Maximum value (only for numeric values).
     * @var mixed
     */
    public $maxValue;

    /**
     * @var string
     * @Enum({"email","url"})
     */
    public $valid;

    /**
     * Regular expression. '/your-expression/'
     * Use {} brackets to not escape your docblock!
     * @var string
     */
    public $regex;

    /**
     * Must be one of the options provided.
     * @var array
     */
    public $enum;

    /**
     * Starts with.
     * @var string
     */
    public $startsWith;

    /**
     * Ends with
     * @var string
     */
    public $endsWith;


    /**
     * Validate constraints.
     * @param $value
     * @return true|array True on success, error will give array that contains error messages.
     */
    public function valid ($value)
    {
        $valid = true;
        $error = array();

        if ($this->minLength !== null && ! (strlen($value) >= $this->minLength)) {
            $valid = false;
            $error[] = 'minimum length';
        }

        if ($this->maxLength !== null && ! (strlen($value) <= $this->maxLength)) {
            $valid = false;
            $error[] = 'maximum length';
        }

        // var_dump(array('cur' => $value, 'min' => $this->minValue));
        if ($this->minValue !== null && (! is_numeric($value) || ! ($value >= $this->minValue))) {
            $valid = false;
            $error[] = 'minimum value';
        }

        if ($this->maxValue !== null && (! is_numeric($value) || ! ($value <= $this->maxValue))) {
            $valid = false;
            $error[] = 'maximum value';
        }

        if ($this->startsWith !== null && ! (strpos($value, $this->startsWith) === 0)) {
            $valid = false;
            $error[] = 'starts with';
        }

        if ($this->endsWith !== null && ! (strpos($value, $this->endsWith) === strlen($value)-strlen($this->endsWith))) {
            $valid = false;
            $error[] = 'ends with';
        }

        if ($this->regex !== null && ! preg_match($this->regex, $value)) {
            $valid = false;
            $error[] = 'valid custom field';
        }

        if ($this->enum !== null && is_array($this->enum)) {
            if (! in_array($value, $this->enum)) {
                $valid = false;
                $error[] = 'is value of list';
            }
        }

        if ($this->valid !== null) {
            switch ($this->valid) {
                case 'email':
                    if (! filter_var($value, FILTER_VALIDATE_EMAIL))
                        $valid = false;
                    break;
                case 'url':
                    if (! filter_var($value, FILTER_VALIDATE_URL))
                        $valid = false;
                    break;
                default:
                    $valid = false;
            }
        }
        if (! $valid) {
            $error[] = 'valid \'' . $this->valid . '\'';
        }

        return $valid === true ? true : $error;
    }
}
