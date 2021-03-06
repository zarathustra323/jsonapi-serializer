<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\JsonApiSerializer\Validator;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use \DateTime;

/**
 * Configuration object for the serializer.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class Configuration
{
    const INTERNAL_NS_DELIM = '\\';

    /**
     * The API host name.
     *
     * @var string
     */
    private $apiHost = 'localhost';

    /**
     * Whether the API utilizes SSL.
     *
     * @var bool
     */
    private $secure = false;

    /**
     * A root endpoint that all resources share.
     *
     * @var string|null
     */
    private $rootEndpoint;

    /**
     * Whether namespaced model links should be built as resource endpoints.
     * Example: Foo\Entity would be built as foo/entity when building a resource link.
     *
     * @var bool
     */
    private $namespacesAsResources = false;

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
     * Validator component for ensuring formats are correct.
     *
     * @var Validator
     */
    private $validator;

    /**
     * Constructor.
     *
     * @param   Validator  $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
        $this->setNamespaceDelimiter('__');
        $this->setEntityNameFormat('dash');
        $this->setFieldKeyFormat('dash');
    }

    /**
     * Gets the validator service.
     *
     * @return  Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the API host name.
     *
     * @return  string
     */
    public function getApiHost()
    {
        return $this->apiHost;
    }

    /**
     * Sets the API host name.
     *
     * @param   string  $apiHost
     * @return  self
     */
    public function setApiHost($apiHost)
    {
        $this->apiHost = $apiHost;
        return $this;
    }

    /**
     * Determines whether the API utilizes SSL.
     *
     * @return  bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Sets whether the API utilizes SSL.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function setSecure($bit = true)
    {
        $this->secure = (Boolean) $bit;
        return $this;
    }

    /**
     * Gets the root API endpoint that all resources utilize.
     *
     * @return  string
     */
    public function getRootEndpoint()
    {
        return $this->rootEndpoint;
    }

    /**
     * Sets the root API endpoint that all resources utilize.
     *
     * @param   string  $rootEndpoint
     * @return  self
     */
    public function setRootEndpoint($rootEndpoint)
    {
        $this->rootEndpoint = $rootEndpoint;
        return $this;
    }

    /**
     * Sets whether namespaced model links should be build as resource endpoints.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function setNamespacesAsResources($bit = true)
    {
        $this->namespacesAsResources = (Boolean) $bit;
        return $this;
    }

    /**
     * Determines whether namespaced model links should be build as resource endpoints.
     *
     * @return  bool
     */
    public function useNamespacesAsResources()
    {
        return $this->namespacesAsResources;
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
        $this->validator->validateNamespaceDelimiter($delim);
        $this->validator->validateNameFormat($delim, $this->getEntityNameFormat());
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
        $this->validator->validateStringFormat($format);
        $this->validator->validateNameFormat($this->getNamespaceDelimiter(), $format);
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
        $this->validator->validateStringFormat($format);
        $this->fieldKeyFormat = $format;
        return $this;
    }
}
