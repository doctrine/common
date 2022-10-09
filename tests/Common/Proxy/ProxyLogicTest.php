<?php

namespace Doctrine\Tests\Common\Proxy;

use Closure;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use function assert;
use function call_user_func_array;
use function class_exists;
use function func_get_args;
use function get_class;
use function method_exists;
use function serialize;
use function unserialize;

/**
 * Test the generated proxies behavior. These tests make assumptions about the structure of LazyLoadableObject
 */
class ProxyLogicTest extends TestCase
{
    /** @var MockObject&ProxyLoader */
    protected $proxyLoader;

    /** @var ClassMetadata */
    protected $lazyLoadableObjectMetadata;

    /** @var LazyLoadableObject&Proxy */
    protected $lazyObject;

    /** @var array<string,string> */
    protected $identifier = [
        'publicIdentifierField' => 'publicIdentifierFieldValue',
        'protectedIdentifierField' => 'protectedIdentifierFieldValue',
    ];

    /** @var MockObject&Callable */
    protected $initializerCallbackMock;

    /**
     * {@inheritDoc}
     */
    public function setUp() : void
    {
        $loader                           = $this->proxyLoader      = $this->createMock(ProxyLoader::class);
        $this->initializerCallbackMock    = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $identifier                       = $this->identifier;
        $this->lazyLoadableObjectMetadata = $metadata = new LazyLoadableObjectClassMetadata();

        // emulating what should happen in a proxy factory
        $cloner = static function (LazyLoadableObject $proxy) use ($loader, $identifier, $metadata) {
            /** @var LazyLoadableObject&Proxy $proxy */
            $proxy = $proxy;
            if ($proxy->__isInitialized()) {
                return;
            }

            $proxy->__setInitialized(true);
            $proxy->__setInitializer(null);
            $original = $loader->load($identifier);

            if ($original === null) {
                throw new UnexpectedValueException();
            }

            foreach ($metadata->getReflectionClass()->getProperties() as $reflProperty) {
                $propertyName = $reflProperty->getName();

                if (! $metadata->hasField($propertyName) && ! $metadata->hasAssociation($propertyName)) {
                    continue;
                }

                $reflProperty->setAccessible(true);
                $reflProperty->setValue($proxy, $reflProperty->getValue($original));
            }
        };

        $proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\LazyLoadableObject';

        // creating the proxy class
        if (! class_exists($proxyClassName, false)) {
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $proxyFileName  = $proxyGenerator->getProxyFileName($metadata->getName());
            $proxyGenerator->generateProxyClass($metadata, $proxyFileName);
            require_once $proxyFileName;
        }

        $this->lazyObject = new $proxyClassName($this->getClosure($this->initializerCallbackMock), $cloner);

        // setting identifiers in the proxy via reflection
        foreach ($metadata->getIdentifierFieldNames() as $idField) {
            $prop = $metadata->getReflectionClass()->getProperty($idField);
            $prop->setAccessible(true);
            $prop->setValue($this->lazyObject, $identifier[$idField]);
        }

        self::assertFalse($this->lazyObject->__isInitialized());
    }

    public function testFetchingPublicIdentifierDoesNotCauseLazyLoading()
    {
        $this->configureInitializerMock(0);

        self::assertSame('publicIdentifierFieldValue', $this->lazyObject->publicIdentifierField);
    }

    public function testFetchingIdentifiersViaPublicGetterDoesNotCauseLazyLoading()
    {
        $this->configureInitializerMock(0);

        self::assertSame('protectedIdentifierFieldValue', $this->lazyObject->getProtectedIdentifierField());
    }

    public function testCallingMethodCausesLazyLoading()
    {
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, 'testInitializationTriggeringMethod', []],
            static function (Proxy $proxy) {
                $proxy->__setInitializer(null);
            }
        );

        $this->lazyObject->testInitializationTriggeringMethod();
        $this->lazyObject->testInitializationTriggeringMethod();
    }

    public function testFetchingPublicFieldsCausesLazyLoading()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__get', ['publicPersistentField']],
            static function () use ($test) {
                $test->setProxyValue('publicPersistentField', 'loadedValue');
            }
        );

        self::assertSame('loadedValue', $this->lazyObject->publicPersistentField);
        self::assertSame('loadedValue', $this->lazyObject->publicPersistentField);
    }

    public function testFetchingPublicAssociationCausesLazyLoading()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__get', ['publicAssociation']],
            static function () use ($test) {
                $test->setProxyValue('publicAssociation', 'loadedAssociation');
            }
        );

        self::assertSame('loadedAssociation', $this->lazyObject->publicAssociation);
        self::assertSame('loadedAssociation', $this->lazyObject->publicAssociation);
    }

    public function testFetchingProtectedAssociationViaPublicGetterCausesLazyLoading()
    {
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, 'getProtectedAssociation', []],
            static function (Proxy $proxy) {
                $proxy->__setInitializer(null);
            }
        );

        self::assertSame('protectedAssociationValue', $this->lazyObject->getProtectedAssociation());
        self::assertSame('protectedAssociationValue', $this->lazyObject->getProtectedAssociation());
    }

    public function testLazyLoadingTriggeredOnlyAtFirstPublicPropertyRead()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__get', ['publicPersistentField']],
            static function () use ($test) {
                $test->setProxyValue('publicPersistentField', 'loadedValue');
                $test->setProxyValue('publicAssociation', 'publicAssociationValue');
            }
        );

        self::assertSame('loadedValue', $this->lazyObject->publicPersistentField);
        self::assertSame('publicAssociationValue', $this->lazyObject->publicAssociation);
    }

    public function testNoticeWhenReadingNonExistentPublicProperties()
    {
        $this->configureInitializerMock(0);

        $class = get_class($this->lazyObject);
        // @todo drop condition when PHPUnit 9.x becomes lowest
        if (method_exists($this, 'expectNotice')) {
            $this->expectNotice();
            $this->expectNoticeMessage('Undefined property: ' . $class . '::$non_existing_property');
        } else {
            $this->expectException(Notice::class);
            $this->expectExceptionMessage('Undefined property: ' . $class . '::$non_existing_property');
        }

        $this->lazyObject->non_existing_property;
    }

    public function testFalseWhenCheckingNonExistentProperty()
    {
        $this->configureInitializerMock(0);

        self::assertFalse(isset($this->lazyObject->non_existing_property));
    }

    public function testNoErrorWhenSettingNonExistentProperty()
    {
        if (PHP_VERSION_ID >= 80200) {
            $this->markTestSkipped('access to a dynamic property trigger a deprecation notice on PHP 8.2+');
        }

        $this->configureInitializerMock(0);

        $this->lazyObject->non_existing_property = 'now has a value';
        self::assertSame('now has a value', $this->lazyObject->non_existing_property);
    }

    public function testCloningCallsClonerWithClonedObject()
    {
        $lazyObject = $this->lazyObject;
        $test       = $this;
        $callback   = static function (LazyLoadableObject $proxy) use ($lazyObject, $test) {
            assert($proxy instanceof Proxy);
            $test->assertNotSame($proxy, $lazyObject);
            $proxy->__setInitializer(null);
            $proxy->publicAssociation = 'clonedAssociation';
        };
        $cb         = $this->createMock(Cloner::class);
        $cb
            ->expects($this->once())
            ->method('cb')
            ->will($this->returnCallback($callback));

        $this->lazyObject->__setCloner($this->getClosure([$cb, 'cb']));

        $cloned = clone $this->lazyObject;
        self::assertSame('clonedAssociation', $cloned->publicAssociation);
        self::assertNotSame($cloned, $lazyObject, 'a clone of the lazy object is retrieved');
    }

    public function cb()
    {
    }

    public function testFetchingTransientPropertiesWillNotTriggerLazyLoading()
    {
        $this->configureInitializerMock(0);

        self::assertSame(
            'publicTransientFieldValue',
            $this->lazyObject->publicTransientField,
            'fetching public transient field won\'t trigger lazy loading'
        );
        $property = $this
            ->lazyLoadableObjectMetadata
            ->getReflectionClass()
            ->getProperty('protectedTransientField');
        $property->setAccessible(true);
        self::assertSame(
            'protectedTransientFieldValue',
            $property->getValue($this->lazyObject),
            'fetching protected transient field via reflection won\'t trigger lazy loading'
        );
    }

    /**
     * Provided to guarantee backwards compatibility
     */
    public function testLoadProxyMethod()
    {
        $this->configureInitializerMock(2, [$this->lazyObject, '__load', []]);

        $this->lazyObject->__load();
        $this->lazyObject->__load();
    }

    public function testLoadingWithPersisterWillBeTriggeredOnlyOnce()
    {
        $this
            ->proxyLoader
            ->expects($this->once())
            ->method('load')
            ->with(
                [
                    'publicIdentifierField' => 'publicIdentifierFieldValue',
                    'protectedIdentifierField' => 'protectedIdentifierFieldValue',
                ],
                $this->lazyObject
            )
                ->will($this->returnCallback(static function ($id, LazyLoadableObject $lazyObject) {
                    // setting a value to verify that the persister can actually set something in the object
                    $lazyObject->publicAssociation = $id['publicIdentifierField'] . '-test';

                    return true;
                }));
        $this->lazyObject->__setInitializer($this->getSuggestedInitializerImplementation());

        $this->lazyObject->__load();
        $this->lazyObject->__load();
        self::assertSame('publicIdentifierFieldValue-test', $this->lazyObject->publicAssociation);
    }

    public function testFailedLoadingWillThrowException()
    {
        $this->proxyLoader->expects($this->any())->method('load')->will($this->returnValue(null));
        $this->expectException(\UnexpectedValueException::class);
        $this->lazyObject->__setInitializer($this->getSuggestedInitializerImplementation());

        $this->lazyObject->__load();
    }

    public function testCloningWithPersister()
    {
        $this->lazyObject->publicTransientField = 'should-not-change';
        $this
            ->proxyLoader
            ->expects($this->exactly(2))
            ->method('load')
            ->with([
                'publicIdentifierField'    => 'publicIdentifierFieldValue',
                'protectedIdentifierField' => 'protectedIdentifierFieldValue',
            ])
                ->will($this->returnCallback(static function () {
                    $blueprint                        = new LazyLoadableObject();
                    $blueprint->publicPersistentField = 'checked-persistent-field';
                    $blueprint->publicAssociation     = 'checked-association-field';
                    $blueprint->publicTransientField  = 'checked-transient-field';

                    return $blueprint;
                }));

        $firstClone = clone $this->lazyObject;
        self::assertSame(
            'checked-persistent-field',
            $firstClone->publicPersistentField,
            'Persistent fields are cloned correctly'
        );
        self::assertSame(
            'checked-association-field',
            $firstClone->publicAssociation,
            'Associations are cloned correctly'
        );
        self::assertSame(
            'should-not-change',
            $firstClone->publicTransientField,
            'Transient fields are not overwritten'
        );

        $secondClone = clone $this->lazyObject;
        self::assertSame(
            'checked-persistent-field',
            $secondClone->publicPersistentField,
            'Persistent fields are cloned correctly'
        );
        self::assertSame(
            'checked-association-field',
            $secondClone->publicAssociation,
            'Associations are cloned correctly'
        );
        self::assertSame(
            'should-not-change',
            $secondClone->publicTransientField,
            'Transient fields are not overwritten'
        );

        // those should not trigger lazy loading
        $firstClone->__load();
        $secondClone->__load();
    }

    public function testNotInitializedProxyUnserialization()
    {
        $this->configureInitializerMock();

        $serialized = serialize($this->lazyObject);
        /** @var LazyLoadableObject&Proxy $unserialized */
        $unserialized = unserialize($serialized);
        $reflClass    = $this->lazyLoadableObjectMetadata->getReflectionClass();

        self::assertFalse($unserialized->__isInitialized(), 'serialization didn\'t cause initialization');

        // Checking identifiers
        self::assertSame('publicIdentifierFieldValue', $unserialized->publicIdentifierField, 'identifiers are kept');
        $protectedIdentifierField = $reflClass->getProperty('protectedIdentifierField');
        $protectedIdentifierField->setAccessible(true);
        self::assertSame(
            'protectedIdentifierFieldValue',
            $protectedIdentifierField->getValue($unserialized),
            'identifiers are kept'
        );

        // Checking transient fields
        self::assertSame(
            'publicTransientFieldValue',
            $unserialized->publicTransientField,
            'transient fields are kept'
        );
        $protectedTransientField = $reflClass->getProperty('protectedTransientField');
        $protectedTransientField->setAccessible(true);
        self::assertSame(
            'protectedTransientFieldValue',
            $protectedTransientField->getValue($unserialized),
            'transient fields are kept'
        );

        // Checking persistent fields
        self::assertSame(
            'publicPersistentFieldValue',
            $unserialized->publicPersistentField,
            'persistent fields are kept'
        );
        $protectedPersistentField = $reflClass->getProperty('protectedPersistentField');
        $protectedPersistentField->setAccessible(true);
        self::assertSame(
            'protectedPersistentFieldValue',
            $protectedPersistentField->getValue($unserialized),
            'persistent fields are kept'
        );

        // Checking associations
        self::assertSame('publicAssociationValue', $unserialized->publicAssociation, 'associations are kept');
        $protectedAssociationField = $reflClass->getProperty('protectedAssociation');
        $protectedAssociationField->setAccessible(true);
        self::assertSame(
            'protectedAssociationValue',
            $protectedAssociationField->getValue($unserialized),
            'associations are kept'
        );
    }

    public function testInitializedProxyUnserialization()
    {
        // persister will retrieve the lazy object itself, so that we don't have to re-define all field values
        $this->proxyLoader->expects($this->once())->method('load')->will($this->returnValue($this->lazyObject));
        $this->lazyObject->__setInitializer($this->getSuggestedInitializerImplementation());
        $this->lazyObject->__load();

        $serialized = serialize($this->lazyObject);
        $reflClass  = $this->lazyLoadableObjectMetadata->getReflectionClass();
        /** @var LazyLoadableObject&Proxy $unserialized */
        $unserialized = unserialize($serialized);

        self::assertTrue($unserialized->__isInitialized(), 'serialization didn\'t cause initialization');

        // Checking transient fields
        self::assertSame(
            'publicTransientFieldValue',
            $unserialized->publicTransientField,
            'transient fields are kept'
        );
        $protectedTransientField = $reflClass->getProperty('protectedTransientField');
        $protectedTransientField->setAccessible(true);
        self::assertSame(
            'protectedTransientFieldValue',
            $protectedTransientField->getValue($unserialized),
            'transient fields are kept'
        );

        // Checking persistent fields
        self::assertSame(
            'publicPersistentFieldValue',
            $unserialized->publicPersistentField,
            'persistent fields are kept'
        );
        $protectedPersistentField = $reflClass->getProperty('protectedPersistentField');
        $protectedPersistentField->setAccessible(true);
        self::assertSame(
            'protectedPersistentFieldValue',
            $protectedPersistentField->getValue($unserialized),
            'persistent fields are kept'
        );

        // Checking identifiers
        self::assertSame(
            'publicIdentifierFieldValue',
            $unserialized->publicIdentifierField,
            'identifiers are kept'
        );
        $protectedIdentifierField = $reflClass->getProperty('protectedIdentifierField');
        $protectedIdentifierField->setAccessible(true);
        self::assertSame(
            'protectedIdentifierFieldValue',
            $protectedIdentifierField->getValue($unserialized),
            'identifiers are kept'
        );

        // Checking associations
        self::assertSame('publicAssociationValue', $unserialized->publicAssociation, 'associations are kept');
        $protectedAssociationField = $reflClass->getProperty('protectedAssociation');
        $protectedAssociationField->setAccessible(true);
        self::assertSame(
            'protectedAssociationValue',
            $protectedAssociationField->getValue($unserialized),
            'associations are kept'
        );
    }

    public function testInitializationRestoresDefaultPublicLazyLoadedFieldValues()
    {
        // setting noop persister
        $this->proxyLoader->expects($this->once())->method('load')->will($this->returnValue($this->lazyObject));
        $this->lazyObject->__setInitializer($this->getSuggestedInitializerImplementation());

        self::assertSame(
            'publicPersistentFieldValue',
            $this->lazyObject->publicPersistentField,
            'Persistent field is restored to default value'
        );
        self::assertSame(
            'publicAssociationValue',
            $this->lazyObject->publicAssociation,
            'Association is restored to default value'
        );
    }

    public function testSettingPublicFieldsCausesLazyLoading()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__set', ['publicPersistentField', 'newPublicPersistentFieldValue']],
            static function () use ($test) {
                $test->setProxyValue('publicPersistentField', 'overrideValue');
                $test->setProxyValue('publicAssociation', 'newAssociationValue');
            }
        );

        $this->lazyObject->publicPersistentField = 'newPublicPersistentFieldValue';
        self::assertSame('newPublicPersistentFieldValue', $this->lazyObject->publicPersistentField);
        self::assertSame('newAssociationValue', $this->lazyObject->publicAssociation);
    }

    public function testSettingPublicAssociationCausesLazyLoading()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__set', ['publicAssociation', 'newPublicAssociationValue']],
            static function () use ($test) {
                $test->setProxyValue('publicPersistentField', 'newPublicPersistentFieldValue');
                $test->setProxyValue('publicAssociation', 'overrideValue');
            }
        );

        $this->lazyObject->publicAssociation = 'newPublicAssociationValue';
        self::assertSame('newPublicAssociationValue', $this->lazyObject->publicAssociation);
        self::assertSame('newPublicPersistentFieldValue', $this->lazyObject->publicPersistentField);
    }

    public function testCheckingPublicFieldsCausesLazyLoading()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__isset', ['publicPersistentField']],
            static function () use ($test) {
                $test->setProxyValue('publicPersistentField', null);
                $test->setProxyValue('publicAssociation', 'setPublicAssociation');
            }
        );

        self::assertFalse(isset($this->lazyObject->publicPersistentField));
        self::assertNull($this->lazyObject->publicPersistentField);
        self::assertTrue(isset($this->lazyObject->publicAssociation));
        self::assertSame('setPublicAssociation', $this->lazyObject->publicAssociation);
    }

    public function testCheckingPublicAssociationCausesLazyLoading()
    {
        $test = $this;
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, '__isset', ['publicAssociation']],
            static function () use ($test) {
                $test->setProxyValue('publicPersistentField', 'newPersistentFieldValue');
                $test->setProxyValue('publicAssociation', 'setPublicAssociation');
            }
        );

        self::assertTrue(isset($this->lazyObject->publicAssociation));
        self::assertSame('setPublicAssociation', $this->lazyObject->publicAssociation);
        self::assertTrue(isset($this->lazyObject->publicPersistentField));
        self::assertSame('newPersistentFieldValue', $this->lazyObject->publicPersistentField);
    }

    public function testCallingVariadicMethodCausesLazyLoading()
    {
        $proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\VariadicTypeHintClass';

        /** @var ClassMetadata&MockObject $metadata */
        $metadata = $this->createMock(ClassMetadata::class);

        $metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(VariadicTypeHintClass::class));
        $metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass(VariadicTypeHintClass::class)));

        // creating the proxy class
        if (! class_exists($proxyClassName, false)) {
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $proxyGenerator->generateProxyClass($metadata, $proxyGenerator->getProxyFileName($metadata->getName()));
            require_once $proxyGenerator->getProxyFileName($metadata->getName());
        }

        $invocationMock = new InvokationSpy();

        /** @var VariadicTypeHintClass $lazyObject */
        $lazyObject = new $proxyClassName(
            static function ($proxy, $method, $parameters) use ($invocationMock) {
                $invocationMock($proxy, $method, $parameters);
            },
            static function () {
            }
        );

        $lazyObject->addType('type1', 'type2');
        self::assertCount(1, $invocationMock->invokations);
        self::assertSame([$lazyObject, 'addType', [['type1', 'type2']]], $invocationMock->invokations[0]);
        self::assertSame(['type1', 'type2'], $lazyObject->types);

        $lazyObject->addTypeWithMultipleParameters('foo', 'bar', 'baz1', 'baz2');
        self::assertCount(2, $invocationMock->invokations);
        self::assertSame(
            [$lazyObject, 'addTypeWithMultipleParameters', ['foo', 'bar', ['baz1', 'baz2']]],
            $invocationMock->invokations[1]
        );
        self::assertSame('foo', $lazyObject->foo);
        self::assertSame('bar', $lazyObject->bar);
        self::assertSame(['baz1', 'baz2'], $lazyObject->baz);
    }

    /**
     * Converts a given callable into a closure
     *
     * @param  callable $callable
     *
     * @return Closure
     */
    public function getClosure($callable)
    {
        return static function () use ($callable) {
            call_user_func_array($callable, func_get_args());
        };
    }

    /**
     * Configures the current initializer callback mock with provided matcher params
     *
     * @param int     $expectedCallCount the number of invocations to be expected. If a value< 0 is provided, `any` is used
     * @param mixed[] $callParamsMatch   an ordered array of parameters to be expected
     * @param Closure $callbackClosure   a return callback closure
     *
     * @return void
     */
    protected function configureInitializerMock(
        $expectedCallCount = 0,
        ?array $callParamsMatch = null,
        ?Closure $callbackClosure = null
    ) {
        if (! $expectedCallCount) {
            $invocationCountMatcher = $this->exactly((int) $expectedCallCount);
        } else {
            $invocationCountMatcher = $expectedCallCount < 0 ? $this->any() : $this->exactly($expectedCallCount);
        }

        $invocationMocker = $this->initializerCallbackMock->expects($invocationCountMatcher)->method('__invoke');

        if ($callParamsMatch !== null) {
            call_user_func_array([$invocationMocker, 'with'], $callParamsMatch);
        }

        if (! $callbackClosure) {
            return;
        }

        $invocationMocker->will($this->returnCallback($callbackClosure));
    }

    /**
     * Sets a value in the current proxy object without triggering lazy loading through `__set`
     *
     * @link https://bugs.php.net/bug.php?id=63463
     *
     * @param string $property
     * @param mixed  $value
     */
    public function setProxyValue($property, $value)
    {
        $reflectionProperty = new ReflectionProperty($this->lazyObject, $property);
        $initializer        = $this->lazyObject->__getInitializer();

        // disabling initializer since setting `publicPersistentField` triggers `__set`/`__get`
        $this->lazyObject->__setInitializer(null);
        $reflectionProperty->setValue($this->lazyObject, $value);
        $this->lazyObject->__setInitializer($initializer);
    }

    /**
     * Retrieves the suggested implementation of an initializer that proxy factories in O*M
     * are currently following, and that should be used to initialize the current proxy object
     *
     * @return Closure
     */
    protected function getSuggestedInitializerImplementation()
    {
        $loader     = $this->proxyLoader;
        $identifier = $this->identifier;

        return static function (LazyLoadableObject $proxy) use ($loader, $identifier) {
            /** @var LazyLoadableObject&Proxy $proxy */
            $proxy = $proxy;
            $proxy->__setInitializer(null);
            $proxy->__setCloner(null);

            if ($proxy->__isInitialized()) {
                return;
            }

            $properties = $proxy->__getLazyProperties();

            foreach ($properties as $propertyName => $property) {
                if (isset($proxy->$propertyName)) {
                    continue;
                }

                $proxy->$propertyName = $properties[$propertyName];
            }

            $proxy->__setInitialized(true);

            if (method_exists($proxy, '__wakeup')) {
                $proxy->__wakeup();
            }

            if ($loader->load($identifier, $proxy) === null) {
                throw new \UnexpectedValueException('Couldn\'t load');
            }
        };
    }
}

interface Cloner
{
    public function cb() : ?callable;
}

interface ProxyLoader
{
    /** @return mixed */
    public function load(...$args);
}
