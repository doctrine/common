<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function class_exists;
use function count;
use function file_get_contents;
use function is_subclass_of;
use function substr_count;

use const PHP_VERSION_ID;

/**
 * Test the proxy generator. Its work is generating on-the-fly subclasses of a given model, which implement the Proxy
 * pattern.
 */
class ProxyGeneratorTest extends TestCase
{
    /** @var string */
    protected $proxyClass = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\LazyLoadableObject';

    /** @var LazyLoadableObjectClassMetadata */
    protected $metadata;

    /** @var ProxyGenerator */
    protected $proxyGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->metadata       = new LazyLoadableObjectClassMetadata();
        $this->proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

        if (class_exists($this->proxyClass, false)) {
            return;
        }

        $this->generateAndRequire($this->proxyGenerator, $this->metadata);
    }

    public function testReferenceProxyRespectsMethodsParametersTypeHinting()
    {
        $method = new ReflectionMethod($this->proxyClass, 'publicTypeHintedMethod');
        $params = $method->getParameters();

        self::assertEquals(1, count($params));
        self::assertInstanceOf(ReflectionNamedType::class, $params[0]->getType());
        self::assertEquals('stdClass', $params[0]->getType()->getName());
    }

    public function testProxyRespectsMethodsWhichReturnValuesByReference()
    {
        $method = new ReflectionMethod($this->proxyClass, 'byRefMethod');

        self::assertTrue($method->returnsReference());
    }

    public function testProxyRespectsByRefMethodParameters()
    {
        $method     = new ReflectionMethod($this->proxyClass, 'byRefParamMethod');
        $parameters = $method->getParameters();
        self::assertSame('thisIsNotByRef', $parameters[0]->getName());
        self::assertFalse($parameters[0]->isPassedByReference());
        self::assertSame('thisIsByRef', $parameters[1]->getName());
        self::assertTrue($parameters[1]->isPassedByReference());
    }

    public function testCreatesAssociationProxyAsSubclassOfTheOriginalOne()
    {
        self::assertTrue(is_subclass_of($this->proxyClass, $this->metadata->getName()));
    }

    public function testNonNamespacedProxyGeneration()
    {
        $classCode = file_get_contents($this->proxyGenerator->getProxyFileName($this->metadata->getName()));

        self::assertStringNotContainsString('class LazyLoadableObject extends \\\\' . $this->metadata->getName(), $classCode);
        self::assertStringContainsString('class LazyLoadableObject extends \\' . $this->metadata->getName(), $classCode);
    }

    public function testClassWithSleepProxyGeneration()
    {
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\SleepClass', false)) {
            $className      = SleepClass::class;
            $metadata       = $this->createClassMetadata($className, ['id']);
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxySleepClass.php');
        self::assertEquals(1, substr_count($classCode, 'function __sleep'));
        self::assertEquals(1, substr_count($classCode, 'parent::__sleep()'));
    }

    /**
     * Check that the proxy doesn't serialize static properties (in __sleep() method)
     *
     * @group DCOM-212
     */
    public function testClassWithStaticPropertyProxyGeneration()
    {
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\StaticPropertyClass', false)) {
            $className      = StaticPropertyClass::class;
            $metadata       = $this->createClassMetadata($className, []);
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyStaticPropertyClass.php');
        self::assertEquals(1, substr_count($classCode, 'function __sleep'));
        self::assertStringNotContainsString('protectedStaticProperty', $classCode);
    }

    private function generateAndRequire($proxyGenerator, $metadata)
    {
        $proxyGenerator->generateProxyClass($metadata, $proxyGenerator->getProxyFileName($metadata->getName()));

        require_once $proxyGenerator->getProxyFileName($metadata->getName());
    }

    public function testClassWithCallableTypeHintOnProxiedMethod()
    {
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\CallableTypeHintClass', false)) {
            $className = CallableTypeHintClass::class;
            $metadata  = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyCallableTypeHintClass.php');

        self::assertEquals(1, substr_count($classCode, 'call(callable $foo)'));
    }

    public function testClassWithVariadicArgumentOnProxiedMethod()
    {
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\VariadicTypeHintClass', false)) {
            $className = VariadicTypeHintClass::class;
            $metadata  = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyVariadicTypeHintClass.php');

        self::assertEquals(1, substr_count($classCode, 'function addType(...$types)'));
        self::assertEquals(1, substr_count($classCode, '__invoke($this, \'addType\', [$types])'));
        self::assertEquals(1, substr_count($classCode, 'parent::addType(...$types)'));
    }

    public function testClassWithScalarTypeHintsOnProxiedMethods()
    {
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\ScalarTypeHintsClass', false)) {
            $className = ScalarTypeHintsClass::class;
            $metadata  = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyScalarTypeHintsClass.php');

        self::assertEquals(1, substr_count($classCode, 'function singleTypeHint(string $param)'));
        self::assertEquals(1, substr_count($classCode, 'function multipleTypeHints(int $a, float $b, bool $c, string $d)'));
        self::assertEquals(1, substr_count($classCode, 'function combinationOfTypeHintsAndNormal(\stdClass $a, \Countable $b, $c, int $d)'));
        self::assertEquals(1, substr_count($classCode, 'function typeHintsWithVariadic(int ...$foo)'));
        self::assertEquals(1, substr_count($classCode, 'function withDefaultValue(int $foo = 123)'));
        self::assertEquals(1, substr_count($classCode, 'function withDefaultValueNull(?int $foo = NULL)'));
    }

    public function testClassWithReturnTypesOnProxiedMethods()
    {
        $className = ReturnTypesClass::class;
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\ReturnTypesClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyReturnTypesClass.php');

        self::assertEquals(1, substr_count($classCode, 'function returnsClass(): \stdClass'));
        self::assertEquals(1, substr_count($classCode, 'function returnsScalar(): int'));
        self::assertEquals(1, substr_count($classCode, 'function returnsArray(): array'));
        self::assertEquals(1, substr_count($classCode, 'function returnsCallable(): callable'));
        self::assertEquals(1, substr_count($classCode, 'function returnsSelf(): \\' . $className));
        self::assertEquals(1, substr_count($classCode, 'function returnsParent(): \stdClass'));
        self::assertEquals(1, substr_count($classCode, 'function returnsInterface(): \Countable'));
    }

    public function testClassWithNullableTypeHintsOnProxiedMethods()
    {
        $className = NullableTypeHintsClass::class;
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableTypeHintsClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableTypeHintsClass.php');

        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintInt(?int $param)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintObject(?\stdClass $param)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintSelf(?\\' . $className . ' $param)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintWithDefault(?int $param = 123)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintWithDefaultNull(?int $param = NULL)'));
        self::assertEquals(1, substr_count($classCode, 'function notNullableTypeHintWithDefaultNull(?int $param = NULL)'));
    }

    public function testClassWithNullableReturnTypesOnProxiedMethods()
    {
        $className = NullableTypeHintsClass::class;
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableTypeHintsClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableTypeHintsClass.php');

        self::assertEquals(1, substr_count($classCode, 'function returnsNullableInt(): ?int'));
        self::assertEquals(1, substr_count($classCode, 'function returnsNullableObject(): ?\stdClass'));
        self::assertEquals(1, substr_count($classCode, 'function returnsNullableSelf(): ?\\' . $className));
    }

    /**
     * @group #751
     */
    public function testClassWithNullableOptionalNonLastParameterOnProxiedMethods()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('signatures with a required parameter following an optional one trigger a deprecation notice on PHP 8.0+');
        }
        $className = NullableNonOptionalHintClass::class;

        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableNonOptionalHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'public function midSignatureNullableParameter(?\stdClass $param = NULL, $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableNonOptionalHintClass.php')
        );

        self::assertStringContainsString(
            'public function midSignatureNotNullableHintedParameter(string $param = \'foo\', $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableNonOptionalHintClass.php')
        );
    }

    /**
     * @group #751
     */
    public function testClassWithPhp71NullableOptionalNonLastParameterOnProxiedMethods()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('signatures with a required parameter following an optional one trigger a deprecation notice on PHP 8.0+');
        }
        $className = Php71NullableDefaultedNonOptionalHintClass::class;

        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php71NullableDefaultedNonOptionalHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'public function midSignatureNullableParameter(?string $param = NULL, $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp71NullableDefaultedNonOptionalHintClass.php'),
            'Signature allows nullable type, although explicit "?" marker isn\'t used in the proxy'
        );

        self::assertStringContainsString(
            'public function midSignatureNotNullableHintedParameter(?string $param = \'foo\', $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp71NullableDefaultedNonOptionalHintClass.php')
        );
    }

    public function testClassWithVoidReturnType()
    {
        $className = VoidReturnTypeClass::class;
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\VoidReturnTypeClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyVoidReturnTypeClass.php');

        self::assertEquals(1, substr_count($classCode, 'function returnsVoid(): void'));
    }

    public function testClassWithIterableTypeHint()
    {
        $className = IterableTypeHintClass::class;
        if (! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\IterableTypeHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyIterableTypeHintClass.php');

        self::assertEquals(1, substr_count($classCode, 'function parameterType(iterable $param)'));
        self::assertEquals(1, substr_count($classCode, 'function returnType(): iterable'));
    }

    public function testClassWithInvalidTypeHintOnProxiedMethod()
    {
        $className      = InvalidTypeHintClass::class;
        $metadata       = $this->createClassMetadata($className, ['id']);
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'The type hint of parameter "foo" in method "invalidTypeHintMethod"'
                . ' in class "' . $className . '" is invalid.'
        );
        $proxyGenerator->generateProxyClass($metadata);
    }

    public function testClassWithInvalidReturnTypeOnProxiedMethod()
    {
        $className      = InvalidReturnTypeClass::class;
        $metadata       = $this->createClassMetadata($className, ['id']);
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'The return type of method "invalidReturnTypeMethod"'
                . ' in class "' . $className . '" is invalid.'
        );
        $proxyGenerator->generateProxyClass($metadata);
    }

    public function testNoConfigDirThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ProxyGenerator(null, null);
    }

    public function testNoNamespaceThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ProxyGenerator(__DIR__ . '/generated', null);
    }

    public function testInvalidPlaceholderThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $generator = new ProxyGenerator(__DIR__ . '/generated', 'SomeNamespace');
        $generator->setPlaceholder('<somePlaceholder>', []);
    }

    public function testUseEvalIfNoFilenameIsGiven()
    {
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

        $proxyGenerator->generateProxyClass($this->createClassMetadata(EvalBase::class, ['id']));

        $reflClass = new ReflectionClass('Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\EvalBase');

        self::assertStringContainsString("eval()'d code", $reflClass->getFileName());
    }

    public function testAbstractClassThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to create a proxy for an abstract class "' . AbstractClass::class . '".');

        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
        $proxyGenerator->generateProxyClass($this->createClassMetadata(AbstractClass::class, []));
    }

    public function testFinalClassThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to create a proxy for a final class "' . FinalClass::class . '".');

        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
        $proxyGenerator->generateProxyClass($this->createClassMetadata(FinalClass::class, []));
    }

    /**
     * @requires PHP >= 8.2.0
     */
    public function testReadOnlyClassThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to create a proxy for a readonly class "' . ReadOnlyClass::class . '".');

        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
        $proxyGenerator->generateProxyClass($this->createClassMetadata(ReadOnlyClass::class, []));
    }

    /**
     * @requires PHP >= 8.0.0
     */
    public function testPhp8CloneWithVoidReturnType()
    {
        $className = Php8MagicCloneClass::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php8MagicCloneClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'public function __clone(): void',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8MagicCloneClass.php')
        );
    }

    /**
     * @requires PHP >= 8.0.0
     */
    public function testPhp8UnionTypes()
    {
        $className = Php8UnionTypes::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php8UnionTypes', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'setValue(\stdClass|array $value): float|bool',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8UnionTypes.php')
        );

        self::assertStringContainsString(
            'setNullableValue(\stdClass|array|null $value): float|bool|null',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8UnionTypes.php')
        );

        self::assertStringContainsString(
            'setNullableValueDefaultNull(\stdClass|array|null $value = NULL): float|bool|null',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8UnionTypes.php')
        );
    }

    /**
     * @requires PHP >= 8.0.0
     */
    public function testPhp8MixedType()
    {
        $className = Php8MixedType::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php8MixedType', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'foo(mixed $bar): mixed',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8MixedType.php')
        );
    }

    /**
     * @requires PHP >= 8.0.0
     */
    public function testPhp8StaticType()
    {
        $className = Php8StaticType::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php8StaticType', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'foo(mixed $bar): static',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8StaticType.php')
        );

        self::assertStringContainsString(
            'fooNull(mixed $bar): ?static',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp8StaticType.php')
        );
    }

    /**
     * @requires PHP >= 8.1.0
     */
    public function testPhp81IntersectionType()
    {
        $className = PHP81IntersectionTypes::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\PHP81IntersectionTypes', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'setFoo(\Traversable&\Countable $foo): \Traversable&\Countable',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81IntersectionTypes.php')
        );
    }

    /**
     * @requires PHP >= 8.1.0
     */
    public function testPhp81NeverType()
    {
        $className = PHP81NeverType::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\PHP81NeverType', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            '__get($name): never',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NeverType.php')
        );

        self::assertStringContainsString(
            '__set($name, $value): never',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NeverType.php')
        );

        self::assertStringContainsString(
            'finishHim(): never',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NeverType.php')
        );
    }

    /**
     * @requires PHP >= 8.1.0
     */
    public function testEnumDefaultInPublicProperty() : void
    {
        $className = Php81EnumPublicPropertyType::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php81EnumPublicPropertyType', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $metadata->method('hasField')->will($this->returnValue(true));

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $this->assertStringContainsString(
            'use Doctrine;',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp81EnumPublicPropertyType.php')
        );

        $object = new \Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\Php81EnumPublicPropertyType();
        $object = unserialize(serialize($object));

        $this->assertSame($object->isEnum, \Doctrine\Tests\Common\Proxy\YesOrNo::YES);
    }

    /**
     * @requires PHP >= 8.1.0
     */
    public function testPhp81NewInInitializers()
    {
        $className = PHP81NewInInitializers::class;

        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\PHP81NewInInitializers', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertStringContainsString(
            'onlyInitializer($foo = new \stdClass()): void',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );

        self::assertStringContainsString(
            'typed(\DateTimeInterface $foo = new \DateTimeImmutable(\'now\')): void',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );

        self::assertStringContainsString(
            'arrayInDefault(array $foo = [new \DateTimeImmutable(\'2022-08-22 16:20\', new \DateTimeZone(\'Europe/Warsaw\'))]): void',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );

        self::assertStringContainsString(
            'scalarConstInDefault(string $foo = \'foo\'): void',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );

        self::assertStringContainsString(
            'constInDefault(array $foo = \Doctrine\Tests\Common\Util\TestAsset\ConstProvider::FOO): void',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );

        self::assertStringContainsString(
            "globalEolInDefault(string \$foo = '\n'): void",
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );

        self::assertStringContainsString(
            "specialCharacterInDefault(string \$foo = '\n'): void",
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPHP81NewInInitializers.php')
        );
    }

    /**
     * @param string  $className
     * @param mixed[] $ids
     *
     * @return MockObject&ClassMetadata
     */
    private function createClassMetadata($className, array $ids)
    {
        $metadata  = $this->createMock(ClassMetadata::class);
        $reflClass = new ReflectionClass($className);
        $metadata->expects($this->any())->method('getReflectionClass')->will($this->returnValue($reflClass));
        $metadata->expects($this->any())->method('getIdentifierFieldNames')->will($this->returnValue($ids));
        $metadata->expects($this->any())->method('getName')->will($this->returnValue($className));

        return $metadata;
    }
}

class EvalBase
{
}
