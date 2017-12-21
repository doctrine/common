<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Proxy\ProxyGenerator;
use ReflectionClass;
use ReflectionMethod;

/**
 * Test the proxy generator. Its work is generating on-the-fly subclasses of a given model, which implement the Proxy
 * pattern.
 *
 * @author Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ProxyGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $proxyClass = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\LazyLoadableObject';

    /**
     * @var LazyLoadableObjectClassMetadata
     */
    protected $metadata;

    /**
     * @var ProxyGenerator
     */
    protected $proxyGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
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
        self::assertEquals('stdClass', $params[0]->getClass()->getName());
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

        self::assertNotContains("class LazyLoadableObject extends \\\\" . $this->metadata->getName(), $classCode);
        self::assertContains("class LazyLoadableObject extends \\" . $this->metadata->getName(), $classCode);
    }

    public function testClassWithSleepProxyGeneration()
    {
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\SleepClass', false)) {
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
     * @group DCOM-212
     */
    public function testClassWithStaticPropertyProxyGeneration()
    {
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\StaticPropertyClass', false)) {
            $className      = StaticPropertyClass::class;
            $metadata       = $this->createClassMetadata($className, []);
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');

            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyStaticPropertyClass.php');
        self::assertEquals(1, substr_count($classCode, 'function __sleep'));
        self::assertNotContains('protectedStaticProperty', $classCode);
    }

    private function generateAndRequire($proxyGenerator, $metadata)
    {
        $proxyGenerator->generateProxyClass($metadata, $proxyGenerator->getProxyFileName($metadata->getName()));

        require_once $proxyGenerator->getProxyFileName($metadata->getName());
    }

    public function testClassWithCallableTypeHintOnProxiedMethod()
    {
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\CallableTypeHintClass', false)) {
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
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\VariadicTypeHintClass', false)) {
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
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\ScalarTypeHintsClass', false)) {
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
        self::assertEquals(1, substr_count($classCode, 'function withDefaultValueNull(int $foo = NULL)'));
    }

    public function testClassWithReturnTypesOnProxiedMethods()
    {
        $className = ReturnTypesClass::class;
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\ReturnTypesClass', false)) {
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
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableTypeHintsClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableTypeHintsClass.php');

        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintInt(?int $param)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintObject(?\stdClass $param)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintSelf(?\\' . $className . ' $param)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintWithDefault(?int $param = 123)'));
        self::assertEquals(1, substr_count($classCode, 'function nullableTypeHintWithDefaultNull(int $param = NULL)'));
        self::assertEquals(1, substr_count($classCode, 'function notNullableTypeHintWithDefaultNull(int $param = NULL)'));
    }

    public function testClassWithNullableReturnTypesOnProxiedMethods()
    {
        $className = NullableTypeHintsClass::class;
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableTypeHintsClass', false)) {
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
        $className = NullableNonOptionalHintClass::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableNonOptionalHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertContains(
            'public function midSignatureNullableParameter(\stdClass $param = NULL, $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableNonOptionalHintClass.php')
        );

        self::assertContains(
            'public function midSignatureNotNullableHintedParameter(string $param = \'foo\', $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableNonOptionalHintClass.php')
        );
    }

    /**
     * @group #751
     */
    public function testClassWithPhp71NullableOptionalNonLastParameterOnProxiedMethods()
    {
        $className = Php71NullableDefaultedNonOptionalHintClass::class;

        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php71NullableDefaultedNonOptionalHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        self::assertContains(
            'public function midSignatureNullableParameter(string $param = NULL, $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp71NullableDefaultedNonOptionalHintClass.php'),
            'Signature allows nullable type, although explicit "?" marker isn\'t used in the proxy'
        );

        self::assertContains(
            'public function midSignatureNotNullableHintedParameter(?string $param = \'foo\', $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp71NullableDefaultedNonOptionalHintClass.php')
        );
    }

    public function testClassWithVoidReturnType()
    {
        $className = VoidReturnTypeClass::class;
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\VoidReturnTypeClass', false)) {
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
        if ( ! class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\IterableTypeHintClass', false)) {
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

        self::assertContains("eval()'d code", $reflClass->getFileName());
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
     * @param       $className
     * @param array $ids
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ClassMetadata
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
