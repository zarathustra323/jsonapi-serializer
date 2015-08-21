<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Metadata configuration object.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class Configuration
{
    /**
     * The external entity namespace delimiter.
     *
     * @var string
     */
    private $namespaceDelimiter;

    /**
     * The external entity name format.
     *
     * @var string
     */
    private $entityNameFormat;

    /**
     * The external entity field key format.
     *
     * @var string
     */
    private $fieldKeyFormat;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setNamespaceDelimiter('/');
        $this->setEntityNameFormat('dash');
        $this->setFieldKeyFormat('camelcase');
    }

    /**
     * Gets the entity namespace delimiter.
     *
     * @return  string
     */
    public function getNamespaceDelimiter()
    {
        return $this->namespaceDelimiter;
    }

    /**
     * Sets the entity namespace delimiter.
     *
     * @param   string  $delim
     * @return  string
     * @throws  InvalidArgumentException
     */
    public function setNamespaceDelimiter($delim)
    {
        $this->validateNamespaceDelimiter($delim);
        $this->validateNameFormat($delim, $this->getEntityNameFormat());
        $this->namespaceDelimiter = $delim;
        return $this;
    }

    /**
     * Gets the string format for entity names.
     *
     * @return  string
     */
    public function getEntityNameFormat()
    {
        return $this->entityNameFormat;
    }

    /**
     * Sets the string format for entity names.
     *
     * @param   string  $format
     * @return  self
     */
    public function setEntityNameFormat($format)
    {
        $this->validateStringFormat($format);
        $this->validateNameFormat($this->getNamespaceDelimiter(), $format);
        $this->entityNameFormat = $format;
        return $this;
    }

    /**
     * Gets the format for entity field name keys.
     *
     * @return  string
     */
    public function getFieldKeyFormat()
    {
        return $this->fieldKeyFormat;
    }

    /**
     * Sets the format for entity field name keys.
     *
     * @param   string  $format
     * @return  self
     */
    public function setFieldKeyFormat($format)
    {
        $this->validateStringFormat($format);
        $this->fieldKeyFormat = $format;
        return $this;
    }

    /**
     * Gets the valid entity namespace delimiters.
     *
     * @todo    Currently the '/' character (U+002F) is considered invalid. Should likely deprecate as a NS char.
     * @return  array
     */
    public function getValidNamespaceDelimiters()
    {
        return ['/', '_', '-'];
    }

    /**
     * Gets the valid entity string formats.
     *
     * @return  array
     */
    public function getValidStringFormats()
    {
        return ['dash', 'camelcase', 'studlycaps', 'underscore'];
    }

    /**
     * Validates the namespace delimiter.
     *
     * @param   string  $delimiter
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateNamespaceDelimiter($delimiter)
    {
        $valid = $this->getValidNamespaceDelimiters();
        if (!in_array($delimiter, $valid)) {
            throw new InvalidArgumentException(sprintf('The namespace delimiter "%s" is invalid. Valid delimiters are "%s"', $delimiter, implode(', ', $valid)));
        }
        return true;
    }

    /**
     * Validates the namespace delimiter compared to the selected name format.
     *
     * @param   string  $delimiter
     * @param   string  $nameFormat
     * @return  bool
     * @throws  InvalidArgumentException
     */
    protected function validateNameFormat($delimiter, $nameFormat)
    {
        if (('_' === $delimiter && 'underscore' === $nameFormat) || ('-' === $delimiter && 'dash' === $nameFormat)) {
            throw new InvalidArgumentException(sprintf('You cannot use the namespace delimiter "%s" when using the entity name format of "%s"', $delimiter, $nameFormat));
        }
        return true;
    }

    /**
     * Validates the the string format for entity and field key names.
     *
     * @param   string  $format
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateStringFormat($format)
    {
        $valid = $this->getValidStringFormats();
        if (!in_array($format, $valid)) {
            throw new InvalidArgumentException(sprintf('The string format "%s" is invalid. Valid formats are "%s"', $format, implode(', ', $valid)));
        }
        return true;
    }
}
