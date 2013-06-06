<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class MagicGetByRefClass
{
    public $valueField;

    /**
     * @param $name
     *
     * @return string
     * @throws \BadMethodCallException
     */
    public function &__get($name)
    {
        if ($name === 'value') {
            return $this->valueField;
        }

        return 'not defined';
    }
}
