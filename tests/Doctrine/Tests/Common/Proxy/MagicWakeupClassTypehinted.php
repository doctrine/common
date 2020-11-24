<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class MagicWakeupClassTypehinted
{
    /** @var string */
    public $id = 'id';

    /** @var string */
    public $publicField = 'publicField';

    /** @var string */
    public $wakeupValue = 'defaultValue';

    public function __wakeup() : void
    {
        $this->wakeupValue = 'newWakeupValue';
    }
}
