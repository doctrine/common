<?php
namespace Doctrine\Tests\Common\Proxy;

use InvalidArgumentException;

/**
 * Test asset class
 *
 * @since 2.4
 */
class MagicGetByRefClass
{
    /**
     * @var mixed
     */
    public $valueField;

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function & __get($name)
    {
        if ($name === 'value') {
            return $this->valueField;
        }

        throw new InvalidArgumentException();
    }
}
