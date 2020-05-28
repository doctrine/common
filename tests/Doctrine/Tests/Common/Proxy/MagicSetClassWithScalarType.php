<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 * @author Jan Barasek <jan@barasek.com>
 */
class MagicSetClassWithScalarType
{
    /**
     * @var string
     */
    public $id = 'id';

    /**
     * @var string
     */
    public $publicField = 'publicField';

    /**
     * @var string|null
     */
    public $testAttribute;

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws \BadMethodCallException
     */
    public function __set(string $name, $value)
    {
        if ($name === 'test') {
            $this->testAttribute = $value;
        }

        if ($name === 'publicField' || $name === 'id') {
            throw new \BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        $this->testAttribute = $value;
    }
}
