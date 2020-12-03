<?php

if (PHP_VERSION_ID < 80000) {
    eval(<<<'BROKEN_CODE'
namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;
    /**
     * Test asset class
     */
    class MagicIssetClassWithInteger
    {
        /** @var string */
        public $id = 'id';

        /** @var string */
        public $publicField = 'publicField';

        /**
         * @throws BadMethodCallException
         */
        public function __isset(string $name) : int
        {
            if ($name === 'test') {
                return 1;
            }

            if ($name === 'publicField' || $name === 'id') {
                throw new BadMethodCallException('Should never be called for "publicField" or "id"');
            }

            return 0;
        }
    }
BROKEN_CODE
    );
}
