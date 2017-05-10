<?php

namespace Doctrine\Common\Persistence;

abstract class AbstractDelegatingObjectManager implements ObjectManager
{
    private $delegate;

    public function __construct(ObjectManager $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * {@inheritDoc}
     */
    public function find($className, $id)
    {
        return $this->delegate->find($className, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function persist($object)
    {
        $this->delegate->persist($object);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($object)
    {
        $this->delegate->remove($object);
    }

    /**
     * {@inheritDoc}
     */
    public function merge($object)
    {
        $this->delegate->merge($object);
    }

    /**
     * {@inheritDoc}
     */
    public function clear($objectName = null)
    {
        $this->delegate->clear($objectName);
    }

    /**
     * {@inheritDoc}
     */
    public function detach($object)
    {
        $this->delegate->detach($object);
    }

    /**
     * {@inheritDoc}
     */
    public function refresh($object)
    {
        $this->delegate->refresh($object);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->delegate->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository($className)
    {
        return $this->delegate->getRepository($className);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata($className)
    {
        return $this->delegate->getClassMetadata($className);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFactory()
    {
        return $this->delegate->getMetadataFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function initializeObject($obj)
    {
        $this->delegate->initializeObject($obj);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($object)
    {
        return $this->delegate->contains($object);
    }

    /**
     * Delegate any other methods that are not defined on the interface, but in
     * the actual implementation.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->delegate, $method), $args);
    }
}