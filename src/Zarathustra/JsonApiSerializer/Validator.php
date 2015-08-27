<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Responsibile for validating common components of the serializer.
 * Common tasks include validating entity name formats, namespaces, strings, etc.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class Validator
{
    /**
     * Valid entity namespace delimiters.
     *
     * @todo    Currently the '/' character (U+002F) is considered invalid. Should likely deprecate as a NS char.
     * @var     array
     */
    private $namespaceDelimiters = ['/', '_', '-'];

    /**
     * Valid entity string formats.
     *
     * @var array
     */
    private $stringFormats = ['dash', 'camelcase', 'studlycaps', 'underscore'];

    /**
     * Validates a namespace delimiter.
     *
     * @param   string  $delimiter
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateNamespaceDelimiter($delimiter)
    {
        if (!in_array($delimiter, $this->namespaceDelimiters)) {
            throw new InvalidArgumentException(sprintf('The namespace delimiter "%s" is invalid. Valid delimiters are "%s"', $delimiter, implode(', ', $this->namespaceDelimiters)));
        }
        return true;
    }

    /**
     * Validates a namespace delimiter compared to a selected name format.
     *
     * @param   string  $delimiter
     * @param   string  $nameFormat
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateNameFormat($delimiter, $nameFormat)
    {
        if (('_' === $delimiter && 'underscore' === $nameFormat) || ('-' === $delimiter && 'dash' === $nameFormat)) {
            throw new InvalidArgumentException(sprintf('You cannot use the namespace delimiter "%s" when using the entity name format of "%s"', $delimiter, $nameFormat));
        }
        return true;
    }

    /**
     * Validates an entity string format.
     *
     * @param   string  $format
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateStringFormat($format)
    {
        if (!in_array($format, $this->stringFormats)) {
            throw new InvalidArgumentException(sprintf('The string format "%s" is invalid. Valid formats are "%s"', $format, implode(', ', $this->stringFormats)));
        }
        return true;
    }
}
