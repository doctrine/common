<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class MagicSleepClassTypehinted
{
    /** @var string */
    public $id = 'id';

    /** @var string */
    public $publicField = 'publicField';

    /** @var string */
    public $serializedField = 'defaultValue';

    /** @var string */
    public $nonSerializedField = 'defaultValue';

    /**
     * @return string[]
     */
    public function __sleep() : array
    {
        return ['serializedField'];
    }
}
