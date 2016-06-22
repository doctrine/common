<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Tests\DoctrineTestCase;

class AbstractManagerRegistryTest extends DoctrineTestCase
{
    /**
     * @var TestManagerRegistry
     */
    private $mr;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            array('default' => 'default_connection'),
            array('default' => 'default_manager'),
            'default',
            'default',
            'Doctrine\Common\Persistence\ObjectManagerAware',
            $this->getManagerFactory()
        );
    }

    public function testResetManager()
    {
        $manager = $this->mr->getManager();
        $newManager = $this->mr->resetManager();

        $this->assertInstanceOf('Doctrine\Common\Persistence\ObjectManager', $newManager);
        $this->assertNotSame($manager, $newManager);
    }

    private function getManagerFactory()
    {
        $that = $this;

        return function () use ($that) {
            return $that->getMock('Doctrine\Common\Persistence\ObjectManager');
        };
    }
}

class TestManagerRegistry extends AbstractManagerRegistry
{
    private $services;

    private $managerFactory;

    public function __construct($name, array $connections, array $managers, $defaultConnection, $defaultManager, $proxyInterfaceName, \Closure $managerFactory)
    {
        $this->managerFactory = $managerFactory;

        parent::__construct($name, $connections, $managers, $defaultConnection, $defaultManager, $proxyInterfaceName);
    }

    protected function getService($name)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = call_user_func($this->managerFactory);
        }

        return $this->services[$name];
    }

    protected function resetService($name)
    {
        unset($this->services[$name]);
    }

    public function getAliasNamespace($alias)
    {
        return __NAMESPACE__;
    }
}
