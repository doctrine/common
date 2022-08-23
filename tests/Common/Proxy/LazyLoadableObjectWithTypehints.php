<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine;
use stdClass as A;

/**
 * Test asset representing a lazy loadable object
 */
class LazyLoadableObjectWithTypehints
{
    /** @var string */
    private $identifierFieldNoReturnTypehint;

    /** @var string */
    private $identifierFieldReturnTypehintScalar;

    /** @var LazyLoadableObjectWithTypehints */
    private $identifierFieldReturnClassFullyQualified;

    /** @var LazyLoadableObjectWithTypehints */
    private $identifierFieldReturnClassPartialUse;

    /** @var LazyLoadableObjectWithTypehints */
    private $identifierFieldReturnClassFullUse;

    /** @var A */
    private $identifierFieldReturnClassOneWord;

    /** @var A */
    private $identifierFieldReturnClassOneLetter;

    /**
     * @return string
     */
    public function getIdentifierFieldNoReturnTypehint()
    {
        return $this->identifierFieldNoReturnTypehint;
    }

    public function getIdentifierFieldReturnTypehintScalar() : string
    {
        return $this->identifierFieldReturnTypehintScalar;
    }

    public function getIdentifierFieldReturnClassFullyQualified() : \Doctrine\Tests\Common\Proxy\LazyLoadableObjectWithTypehints
    {
        return $this->identifierFieldReturnClassFullyQualified;
    }

    public function getIdentifierFieldReturnClassPartialUse() : Doctrine\Tests\Common\Proxy\LazyLoadableObjectWithTypehints
    {
        return $this->identifierFieldReturnClassPartialUse;
    }

    public function getIdentifierFieldReturnClassFullUse() : LazyLoadableObjectWithTypehints
    {
        return $this->identifierFieldReturnClassFullUse;
    }

    public function getIdentifierFieldReturnClassOneWord() : A
    {
        return $this->identifierFieldReturnClassOneWord;
    }

    public function getIdentifierFieldReturnClassOneLetter() : A
    {
        return $this->identifierFieldReturnClassOneLetter;
    }
}
