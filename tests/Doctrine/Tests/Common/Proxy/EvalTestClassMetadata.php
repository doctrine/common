<?php

namespace Doctrine\Tests\Common\Proxy;

class EvalTestClassMetadata extends LazyLoadableObjectClassMetadata
{
    /**
     * {@inheritDoc}
     */
    public function getReflectionClass()
    {
        if (null === $this->reflectionClass) {
            $this->reflectionClass = new \ReflectionClass(__NAMESPACE__ . '\EvalTestClass');
        }

        return $this->reflectionClass;
    }
}
