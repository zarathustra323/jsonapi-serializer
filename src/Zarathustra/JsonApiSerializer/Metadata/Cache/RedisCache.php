<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Cache;

use Redis;
use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Caches and retrieves EntityMetadata objects from Redis.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class RedisCache implements CacheInterface
{
    const SERIALIZER_PHP = 0;
    const SERIALIZER_IGBINARY = 1;

    /**
     * The Redis instance.
     * Assumes that the connection has been established and the proper database has been selected.
     *
     * @var Redis
     */
    private $redis;

    /**
     * The TTL of the cache, in seconds.
     *
     * @var int
     */
    private $ttl;

    /**
     * The serializer to use.
     *
     * @var int
     */
    private $serializer;

    /**
     * The cache key prefix to use.
     *
     * @var string
     */
     private $prefix = 'JsonApi';

    /**
     * Constructor.
     *
     * @param   Redis   $redis
     * @param   int     $ttl
     * @param   int     $serializer
     */
    public function __construct(Redis $redis, $ttl = 3600, $serializer = self::SERIALIZER_PHP)
    {
        $this->redis = $redis;
        $this->ttl = (Integer) $ttl;
        $this->serializer = $serializer;
    }

    /**
     * Sets a custom cache key prefix.
     *
     * @param   string  $prefix
     * @return  self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Gets the cache key based on entity type.
     *
     * @param   string  $type
     * @return  string
     */
    protected function getKey($type)
    {
        return sprintf('%s::%s', $this->prefix, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataFromCache($type)
    {
        $result = $this->redis->get($this->getKey($type));
        if (!$result) {
            return null;
        }
        $metadata = $this->unserialize($result);
        return $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function putMetadataInCache(EntityMetadata $metadata)
    {
        $value = $this->serialize($metadata);
        $r = $this->redis->setex($this->getKey($metadata->type), $this->ttl, $value);
        return $this;
    }

    /**
     * Serializes the metadata object based on the selected serializer.
     *
     * @param   EntityMetadata  $metadata
     * @return  string
     * @throws  InvalidArgumentException
     */
    private function serialize(EntityMetadata $metadata)
    {
        switch ($this->serializer) {
            case self::SERIALIZER_PHP:
                return serialize($metadata);
            case self::SERIALIZER_IGBINARY:
                return igbinary_serialize($metadata);
            default:
                throw new InvalidArgumentException('Unable to handle serialization of the metadata object');
        }
    }

    /**
     * Unserializes the metadata object based on the selected serializer.
     *
     * @param   string  $value
     * @return  string
     * @throws  InvalidArgumentException
     */
    private function unserialize($value)
    {
        switch ($this->serializer) {
            case self::SERIALIZER_PHP:
                return unserialize($value);
            case self::SERIALIZER_IGBINARY:
                return igbinary_unserialize($value);
            default:
                throw new InvalidArgumentException('Unable to handle unserialization of the metadata object');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function evictMetadataFromCache(EntityMetadata $metadata)
    {
        $this->redis->delete($this->getKey($metadata->type));
    }
}
