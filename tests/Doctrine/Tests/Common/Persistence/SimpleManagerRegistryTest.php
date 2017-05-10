<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\SimpleManagerRegistry;

class SimpleManagerRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetManager()
    {
        $con = new \stdClass;
        $em = new \stdClass;
        $calls = 0;

        $registry = new SimpleManagerRegistry(function($name) use ($con, $em, &$calls) {
            $calls += 1;

            switch ($name) {
                case 'default_connection':
                    return $con;

                case 'default_manager':
                    return $em;

                default:
                    throw new \LogicException(sprintf('Unknown service "%s".', $name));
            }
        });

        $this->assertSame($con, $registry->getConnection());
        $this->assertSame($con, $registry->getConnection('default'));
        $this->assertSame($em, $registry->getManager());
        $this->assertSame($em, $registry->getManager('default'));
        $this->assertEquals(2, $calls);

        $registry->resetManager();
        $this->assertSame($em, $registry->getManager());
        $this->assertEquals(3, $calls);
    }
}