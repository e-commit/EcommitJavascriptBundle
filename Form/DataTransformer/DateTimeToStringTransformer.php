<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\DataTransformer;

use Symfony\Component\Form\Extension\Core\DataTransformer\BaseDateTimeTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class DateTimeToStringTransformer extends BaseDateTimeTransformer
{
    private $format;

    /**
     * Transforms a \DateTime instance to a string
     *
     * @see \DateTime::format() for supported formats
     *
     * @param string $inputTimezone  The name of the input timezone
     * @param string $outputTimezone The name of the output timezone
     * @param string $format         The date format
     *
     * @throws UnexpectedTypeException if a timezone is not a string
     */
    public function __construct($inputTimezone = null, $outputTimezone = null, $format = 'Y-m-d H:i:s')
    {
        parent::__construct($inputTimezone, $outputTimezone);

        $this->format = $format;
    }

    /**
     * Transforms a DateTime object into a date string with the configured format
     * and timezone
     *
     * @param  DateTime $value  A DateTime object
     *
     * @return string           A value as produced by PHP's date() function
     *
     * @throws UnexpectedTypeException if the given value is not a \DateTime instance
     * @throws TransformationFailedException if the output timezone is not supported
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof \DateTime) {
            throw new UnexpectedTypeException($value, '\DateTime');
        }

        $value = clone $value;
        try {
            $value->setTimezone(new \DateTimeZone($this->outputTimezone));
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return $value->format($this->format);
    }

    /**
     * Transforms a date string in the configured timezone into a DateTime object.
     *
     * @param  string $value  A value as produced by PHP's date() function
     *
     * @return \DateTime      An instance of \DateTime
     *
     * @throws UnexpectedTypeException if the given value is not a string
     * @throws TransformationFailedException if the date could not be parsed
     * @throws TransformationFailedException if the input timezone is not supported
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        try {
            $dateTime = \DateTime::createFromFormat($this->format, $value, new \DateTimeZone($this->outputTimezone));
            $errors = \DateTime::getLastErrors();
            if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
                throw new \Exception('Date is invalid. List of warnings: "' . implode(', "',array_values($errors['warnings'])) . '" ' .
                        'List of errors: "' . implode(', "',array_values($errors['errors'])) . '"');
            }

            // Force value to be in same format as given to transform
            if ($value !== $dateTime->format($this->format)) {
                $dateTime = new \DateTime($dateTime->format($this->format), new \DateTimeZone($this->outputTimezone));
            }

            if ($this->inputTimezone !== $this->outputTimezone) {
                $dateTime->setTimeZone(new \DateTimeZone($this->inputTimezone));
            }
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid date');
        }

        return $dateTime;
    }
}