<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 * @author Jan Barasek <jan@barasek.com>
 */
class MagicGetClassWithScalarType
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
     * @param string $name
     *
     * @return string
     * @throws \BadMethodCallException
     */
    public function __get(string $name): string
    {
        if ($name === 'test') {
            return 'test';
        }

        if ($name === 'publicField' || $name === 'id') {
            throw new \BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        return 'not defined';
    }
}
