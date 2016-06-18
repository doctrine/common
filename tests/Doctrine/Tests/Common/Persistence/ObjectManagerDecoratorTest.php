<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\ObjectManagerDecorator;
use Doctrine\Common\Persistence\ObjectManager;

class NullObjectManagerDecorator extends ObjectManagerDecorator
{
    public function __construct(ObjectManager $wrapped)
    {
        $this->wrapped = $wrapped;
    }
}

class ObjectManagerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    private $wrapped;
    private $decorated;

    public function setUp()
    {
        $this->wrapped   = $this->createMock(ObjectManager::class);
        $this->decorated = new NullObjectManagerDecorator($this->wrapped);
    }

    public function getMethodParameters()
    {
        $class = new \ReflectionClass(ObjectManager::class);

        $methods = [];
        foreach ($class->getMethods() as $method) {
            if ($method->getNumberOfRequiredParameters() === 0) {
               $methods[] = [$method->getName(), []];
            } elseif ($method->getNumberOfRequiredParameters() > 0) {
                $methods[] = [$method->getName(), array_fill(0, $method->getNumberOfRequiredParameters(), 'req') ?: []];
            }
            if ($method->getNumberOfParameters() != $method->getNumberOfRequiredParameters()) {
                $methods[] = [$method->getName(), array_fill(0, $method->getNumberOfParameters(), 'all') ?: []];
            }
        }

        return $methods;
    }

    /**
     * @dataProvider getMethodParameters
     */
    public function testAllMethodCallsAreDelegatedToTheWrappedInstance($method, array $parameters)
    {
        $stub = $this->wrapped
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue('INNER VALUE FROM ' . $method));

        call_user_func_array([$stub, 'with'], $parameters);

        $this->assertSame('INNER VALUE FROM ' . $method, call_user_func_array([$this->decorated, $method], $parameters));
    }
}