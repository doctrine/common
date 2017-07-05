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
               $methods[] = [$method->getName(), [], $this->getDummyReturnType($method)];
            } elseif ($method->getNumberOfRequiredParameters() > 0) {
                $methods[] = [$method->getName(), $this->buildDummyParameters($method, true), $this->getDummyReturnType($method)];
            }
            if ($method->getNumberOfParameters() != $method->getNumberOfRequiredParameters()) {
                $methods[] = [$method->getName(), $this->buildDummyParameters($method), $this->getDummyReturnType($method)];
            }
        }

        return $methods;
    }

    /**
     * @dataProvider getMethodParameters
     */
    public function testAllMethodCallsAreDelegatedToTheWrappedInstance($method, array $parameters, $returnedValue)
    {
        $stub = $this->wrapped
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($returnedValue));

        call_user_func_array([$stub, 'with'], $parameters);

        $this->assertSame($returnedValue, call_user_func_array([$this->decorated, $method], $parameters));
    }

    private function buildDummyParameters(\ReflectionMethod $method, bool $requiredOnly = false): array
    {
        if ($method->getNumberOfParameters() === 0) {
            return [];
        }

        $dummies = [];
        for (
            $i = 0, $count = $requiredOnly ? $method->getNumberOfRequiredParameters() : $method->getNumberOfParameters();
            $i < $count;
            $i++
        ) {
            $dummies[] = $this->getDummyValueForParameter($method->getParameters()[$i]);
        }

        return $dummies;
    }

    private function getDummyValueForParameter(\ReflectionParameter $parameter)
    {
        if ($parameter->getType() === null) {
            // mixed
            return 'untyped';
        }

        return $this->getDummyValueForType($parameter->getType());
    }

    private function getDummyReturnType(\ReflectionMethod $method)
    {
        if (! $method->hasReturnType()) {
            return 'untyped';
        }

        return $this->getDummyValueForType($method->getReturnType());
    }

    private function getDummyValueForType(\ReflectionType $type)
    {
        if ($type->allowsNull()) {
            return null;
        }

        switch ((string) $type) {
            case 'object':
                return new \stdClass();
            case 'string':
                return'php';
            case 'bool':
                return true;
            case 'int':
                return 42;
            case 'float':
                return 4.2;
            case 'array':
                return [];
            case 'void':
                return null;
            default:
                return $this->createMock((string) $type);
        }
    }
}
