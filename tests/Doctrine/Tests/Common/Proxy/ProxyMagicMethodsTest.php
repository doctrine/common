<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function class_exists;
use function in_array;
use function serialize;
use function sprintf;
use function unserialize;

/**
 * Test for behavior of proxies with inherited magic methods
 */
class ProxyMagicMethodsTest extends TestCase
{
    /** @var ProxyGenerator */
    protected $proxyGenerator;

    /** @var LazyLoadableObject|Proxy */
    protected $lazyObject;

    protected $identifier = [
        'publicIdentifierField' => 'publicIdentifierFieldValue',
        'protectedIdentifierField' => 'protectedIdentifierFieldValue',
    ];

    /** @var MockObject #Callable */
    protected $initializerCallbackMock;

    /**
     * {@inheritDoc}
     */
    public function setUp() : void
    {
        $this->proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . '\\MagicMethodProxy');
    }

    public static function tearDownAfterClass() : void
    {
    }

    public function testInheritedMagicGet()
    {
        $proxyClassName = $this->generateProxyClass(MagicGetClass::class);
        $proxy          = new $proxyClassName(
            static function (Proxy $proxy, $method, $params) use (&$counter) {
                if (! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);
            }
        );

        self::assertSame('id', $proxy->id);
        self::assertSame('modifiedPublicField', $proxy->publicField);
        self::assertSame('test', $proxy->test);
        self::assertSame('not defined', $proxy->notDefined);

        self::assertSame(3, $counter);
    }

    public function testInheritedMagicGetWithScalarType()
    {
        $proxyClassName = $this->generateProxyClass(MagicGetClassWithScalarType::class);
        $proxy          = new $proxyClassName(
            static function (Proxy $proxy, $method, $params) use (&$counter) {
                if (! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);
            }
        );

        self::assertSame('id', $proxy->id);
        self::assertSame('modifiedPublicField', $proxy->publicField);
        self::assertSame('test', $proxy->test);
        self::assertSame('not defined', $proxy->notDefined);

        self::assertSame(3, $counter);
    }

    public function testInheritedMagicGetWithScalarTypeAndRenamedParameter()
    {
        $proxyClassName = $this->generateProxyClass(MagicGetClassWithScalarTypeAndRenamedParameter::class);
        $proxy          = new $proxyClassName(
            static function (Proxy $proxy, $method, $params) use (&$counter) {
                if (! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);
            }
        );

        self::assertSame('id', $proxy->id);
        self::assertSame('modifiedPublicField', $proxy->publicField);
        self::assertSame('test', $proxy->test);
        self::assertSame('not defined', $proxy->notDefined);

        self::assertSame(3, $counter);
    }

    public function testInheritedMagicGetWithVoid()
    {
        $proxyClassName = $this->generateProxyClass(MagicGetClassWithVoid::class);
        $proxy          = new $proxyClassName(static function (Proxy $proxy, $method, $params) use (&$counter) {
            if (in_array($params[0], ['publicField', 'test'])) {
                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);

                return;
            }

            throw new InvalidArgumentException(
                sprintf('Should not be initialized when checking isset("%s")', $params[0])
            );
        });

        self::assertNull($proxy->publicField);
        self::assertNull($proxy->test);

        self::assertSame(2, $counter);
    }

    /**
     * @group DCOM-194
     */
    public function testInheritedMagicGetByRef()
    {
        $proxyClassName = $this->generateProxyClass(MagicGetByRefClass::class);
        /** @var MagicGetByRefClass $proxy */
        $proxy             = new $proxyClassName();
        $proxy->valueField = 123;
        $value             = & $proxy->__get('value');

        self::assertSame(123, $value);

        $value = 456;

        self::assertSame(456, $proxy->__get('value'), 'Value was fetched by reference');

        $this->expectException(InvalidArgumentException::class);

        $undefined = $proxy->nonExisting;
    }

    public function testInheritedMagicSet()
    {
        $proxyClassName = $this->generateProxyClass(MagicSetClass::class);
        $proxy          = new $proxyClassName(
            static function (Proxy $proxy, $method, $params) use (&$counter) {
                if (! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $counter += 1;
            }
        );

        self::assertSame('id', $proxy->id);

        $proxy->publicField = 'publicFieldValue';

        self::assertSame('publicFieldValue', $proxy->publicField);

        $proxy->test = 'testValue';

        self::assertSame('testValue', $proxy->testAttribute);

        $proxy->notDefined = 'not defined';

        self::assertSame('not defined', $proxy->testAttribute);
        self::assertSame(3, $counter);
    }

    public function testInheritedMagicSetWithScalarType()
    {
        $proxyClassName = $this->generateProxyClass(MagicSetClassWithScalarType::class);
        $proxy          = new $proxyClassName(
            static function (Proxy $proxy, $method, $params) use (&$counter) {
                if (! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $counter += 1;
            }
        );

        self::assertSame('id', $proxy->id);

        $proxy->publicField = 'publicFieldValue';

        self::assertSame('publicFieldValue', $proxy->publicField);

        $proxy->test = 'testValue';

        self::assertSame('testValue', $proxy->testAttribute);

        $proxy->notDefined = 'not defined';

        self::assertSame('not defined', $proxy->testAttribute);
        self::assertSame(3, $counter);
    }

    public function testInheritedMagicSetWithScalarTypeAndRenamedParameters()
    {
        $proxyClassName = $this->generateProxyClass(MagicSetClassWithScalarTypeAndRenamedParameters::class);
        $proxy          = new $proxyClassName(
            static function (Proxy $proxy, $method, $params) use (&$counter) {
                if (! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $counter += 1;
            }
        );

        self::assertSame('id', $proxy->id);

        $proxy->publicField = 'publicFieldValue';

        self::assertSame('publicFieldValue', $proxy->publicField);

        $proxy->test = 'testValue';

        self::assertSame('testValue', $proxy->testAttribute);

        $proxy->notDefined = 'not defined';

        self::assertSame('not defined', $proxy->testAttribute);
        self::assertSame(3, $counter);
    }

    public function testInheritedMagicSleep()
    {
        $proxyClassName = $this->generateProxyClass(MagicSleepClass::class);
        $proxy          = new $proxyClassName();

        self::assertSame('defaultValue', $proxy->serializedField);
        self::assertSame('defaultValue', $proxy->nonSerializedField);

        $proxy->serializedField    = 'changedValue';
        $proxy->nonSerializedField = 'changedValue';

        $unserialized = unserialize(serialize($proxy));

        self::assertSame('changedValue', $unserialized->serializedField);
        self::assertSame('defaultValue', $unserialized->nonSerializedField, 'Field was not returned by "__sleep"');
    }

    public function testInheritedMagicWakeup()
    {
        $proxyClassName = $this->generateProxyClass(MagicWakeupClass::class);
        $proxy          = new $proxyClassName();

        self::assertSame('defaultValue', $proxy->wakeupValue);

        $proxy->wakeupValue = 'changedValue';
        $unserialized       = unserialize(serialize($proxy));

        self::assertSame('newWakeupValue', $unserialized->wakeupValue, '"__wakeup" was called');

        $unserialized->__setInitializer(static function (Proxy $proxy) {
            $proxy->__setInitializer(null);

            $proxy->publicField = 'newPublicFieldValue';
        });

        self::assertSame('newPublicFieldValue', $unserialized->publicField, 'Proxy can still be initialized');
    }

    public function testInheritedMagicIsset()
    {
        $proxyClassName = $this->generateProxyClass(MagicIssetClass::class);
        $proxy          = new $proxyClassName(static function (Proxy $proxy, $method, $params) use (&$counter) {
            if (in_array($params[0], ['publicField', 'test', 'nonExisting'])) {
                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);

                return;
            }

            throw new InvalidArgumentException(
                sprintf('Should not be initialized when checking isset("%s")', $params[0])
            );
        });

        self::assertTrue(isset($proxy->id));
        self::assertTrue(isset($proxy->publicField));
        self::assertTrue(isset($proxy->test));
        self::assertFalse(isset($proxy->nonExisting));

        self::assertSame(3, $counter);
    }

    public function testInheritedMagicIssetWithBoolean()
    {
        $proxyClassName = $this->generateProxyClass(MagicIssetClassWithBoolean::class);
        $proxy          = new $proxyClassName(static function (Proxy $proxy, $method, $params) use (&$counter) {
            if (in_array($params[0], ['publicField', 'test', 'nonExisting'])) {
                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);

                return;
            }

            throw new InvalidArgumentException(
                sprintf('Should not be initialized when checking isset("%s")', $params[0])
            );
        });

        self::assertTrue(isset($proxy->id));
        self::assertTrue(isset($proxy->publicField));
        self::assertTrue(isset($proxy->test));
        self::assertFalse(isset($proxy->nonExisting));

        self::assertSame(3, $counter);
    }

    public function testInheritedMagicIssetWithInteger()
    {
        $proxyClassName = $this->generateProxyClass(MagicIssetClassWithInteger::class);
        $proxy          = new $proxyClassName(static function (Proxy $proxy, $method, $params) use (&$counter) {
            if (in_array($params[0], ['publicField', 'test', 'nonExisting'])) {
                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter           += 1;

                $proxy->__setInitializer($initializer);

                return;
            }

            throw new InvalidArgumentException(
                sprintf('Should not be initialized when checking isset("%s")', $params[0])
            );
        });

        self::assertTrue(isset($proxy->id));
        self::assertTrue(isset($proxy->publicField));
        self::assertTrue(isset($proxy->test));
        self::assertFalse(isset($proxy->nonExisting));

        self::assertSame(3, $counter);
    }

    public function testInheritedMagicClone()
    {
        $proxyClassName = $this->generateProxyClass(MagicCloneClass::class);
        $proxy          = new $proxyClassName(
            null,
            static function ($proxy) {
                $proxy->cloned = true;
            }
        );

        $cloned = clone $proxy;

        self::assertSame('newClonedValue', $cloned->clonedValue);
        self::assertFalse($proxy->cloned);
        self::assertTrue($cloned->cloned);
    }

    /**
     * @group DCOM-175
     */
    public function testClonesPrivateProperties()
    {
        $proxyClassName = $this->generateProxyClass(SerializedClass::class);
        /** @var SerializedClass $proxy */
        $proxy = new $proxyClassName();

        $proxy->setFoo(1);
        $proxy->setBar(2);
        $proxy->setBaz(3);

        $unserialized = unserialize(serialize($proxy));

        self::assertSame(1, $unserialized->getFoo());
        self::assertSame(2, $unserialized->getBar());
        self::assertSame(3, $unserialized->getBaz());
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private function generateProxyClass($className)
    {
        $proxyClassName = 'Doctrine\\Tests\\Common\\Proxy\\MagicMethodProxy\\__CG__\\' . $className;

        if (class_exists($proxyClassName, false)) {
            return $proxyClassName;
        }

        $metadata = $this->createMock(ClassMetadata::class);

        $metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($className));

        $metadata
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(['id']));

        $metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($className)));

        $metadata
            ->expects($this->any())
            ->method('isIdentifier')
            ->will($this->returnCallback(static function ($fieldName) {
                return $fieldName === 'id';
            }));

        $metadata
            ->expects($this->any())
            ->method('hasField')
            ->will($this->returnCallback(static function ($fieldName) {
                return in_array($fieldName, ['id', 'publicField']);
            }));

        $metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'publicField']));

        $metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->will($this->returnValue('string'));

        $this->proxyGenerator->generateProxyClass($metadata, $this->proxyGenerator->getProxyFileName($className));
        require_once $this->proxyGenerator->getProxyFileName($className);

        return $proxyClassName;
    }
}
