<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure\Traits;

trait MetaEnabled
{
    /**
     * Custom meta object.
     *
     * @var Meta
     */
    protected $meta;

    /**
     * Gets the custom meta object.
     *
     * @return  Meta|null
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Sets the custom meta object.
     *
     * @param   Meta
     * @return  self
     */
    public function setMeta(Meta $meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Determines if a custom meta object has been set.
     *
     * @return  bool
     */
    public function hasMeta()
    {
        return null !== $this->getMeta();
    }
}
