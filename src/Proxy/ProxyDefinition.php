<?php

namespace Doctrine\Common\Proxy;

use ReflectionProperty;

/**
 * Definition structure how to create a proxy.
 *
 * @deprecated The ProxyDefinition class is deprecated since doctrine/common 3.5.
 */
class ProxyDefinition
{
    /** @var string */
    public $proxyClassName;

    /** @var array<string> */
    public $identifierFields;

    /** @var ReflectionProperty[] */
    public $reflectionFields;

    /** @var callable */
    public $initializer;

    /** @var callable */
    public $cloner;

    /**
     * @param string                            $proxyClassName
     * @param array<string>                     $identifierFields
     * @param array<string, ReflectionProperty> $reflectionFields
     * @param callable                          $initializer
     * @param callable                          $cloner
     */
    public function __construct($proxyClassName, array $identifierFields, array $reflectionFields, $initializer, $cloner)
    {
        $this->proxyClassName   = $proxyClassName;
        $this->identifierFields = $identifierFields;
        $this->reflectionFields = $reflectionFields;
        $this->initializer      = $initializer;
        $this->cloner           = $cloner;
    }
}
