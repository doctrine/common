<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */


namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Proxy\ProxyGenerator;
use ReflectionClass;
use ReflectionMethod;
use PHPUnit_Framework_TestCase;

/**
 * Test the proxy generator. Its work is generating on-the-fly subclasses of a given model, which implement the Proxy
 * pattern.
 *
 * @author Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ProxyGeneratorTest extends PHPUnit_Framework_TestCase
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
        $this->proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);

        if (class_exists($this->proxyClass, false)) {
            return;
        }

        $this->generateAndRequire($this->proxyGenerator, $this->metadata);
    }

    public function testReferenceProxyRespectsMethodsParametersTypeHinting()
    {
        $method = new ReflectionMethod($this->proxyClass, 'publicTypeHintedMethod');
        $params = $method->getParameters();

        $this->assertEquals(1, count($params));
        $this->assertEquals('stdClass', $params[0]->getClass()->getName());
    }

    public function testProxyRespectsMethodsWhichReturnValuesByReference()
    {
        $method = new ReflectionMethod($this->proxyClass, 'byRefMethod');

        $this->assertTrue($method->returnsReference());
    }

    public function testProxyRespectsByRefMethodParameters()
    {
        $method = new ReflectionMethod($this->proxyClass, 'byRefParamMethod');
        $parameters = $method->getParameters();
        $this->assertSame('thisIsNotByRef', $parameters[0]->getName());
        $this->assertFalse($parameters[0]->isPassedByReference());
        $this->assertSame('thisIsByRef', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->isPassedByReference());
    }

    public function testCreatesAssociationProxyAsSubclassOfTheOriginalOne()
    {
        $this->assertTrue(is_subclass_of($this->proxyClass, $this->metadata->getName()));
    }

    public function testNonNamespacedProxyGeneration()
    {
        $classCode = file_get_contents($this->proxyGenerator->getProxyFileName($this->metadata->getName()));

        $this->assertNotContains("class LazyLoadableObject extends \\\\" . $this->metadata->getName(), $classCode);
        $this->assertContains("class LazyLoadableObject extends \\" . $this->metadata->getName(), $classCode);
    }

    public function testClassWithSleepProxyGeneration()
    {
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\SleepClass', false)) {
            $className = SleepClass::class;
            $metadata = $this->createClassMetadata($className, ['id']);
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);

            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxySleepClass.php');
        $this->assertEquals(1, substr_count($classCode, 'function __sleep'));
        $this->assertEquals(1, substr_count($classCode, 'parent::__sleep()'));
    }

    /**
     * Check that the proxy doesn't serialize static properties (in __sleep() method)
     * @group DCOM-212
     */
    public function testClassWithStaticPropertyProxyGeneration()
    {
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\StaticPropertyClass', false)) {
            $className = StaticPropertyClass::class;
            $metadata = $this->createClassMetadata($className, []);
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);

            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyStaticPropertyClass.php');
        $this->assertEquals(1, substr_count($classCode, 'function __sleep'));
        $this->assertNotContains('protectedStaticProperty', $classCode);
    }

    private function generateAndRequire($proxyGenerator, $metadata)
    {
        $proxyGenerator->generateProxyClass($metadata, $proxyGenerator->getProxyFileName($metadata->getName()));

        require_once $proxyGenerator->getProxyFileName($metadata->getName());
    }

    public function testClassWithCallableTypeHintOnProxiedMethod()
    {
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\CallableTypeHintClass', false)) {
            $className = CallableTypeHintClass::class;
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyCallableTypeHintClass.php');

        $this->assertEquals(1, substr_count($classCode, 'call(callable $foo)'));
    }

    public function testClassWithVariadicArgumentOnProxiedMethod()
    {
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\VariadicTypeHintClass', false)) {
            $className = VariadicTypeHintClass::class;
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyVariadicTypeHintClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function addType(...$types)'));
        $this->assertEquals(1, substr_count($classCode, '__invoke($this, \'addType\', [$types])'));
        $this->assertEquals(1, substr_count($classCode, 'parent::addType(...$types)'));
    }

    /**
     * @requires PHP 7.0
     */
    public function testClassWithScalarTypeHintsOnProxiedMethods()
    {
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\ScalarTypeHintsClass', false)) {
            $className = ScalarTypeHintsClass::class;
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyScalarTypeHintsClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function singleTypeHint(string $param)'));
        $this->assertEquals(1, substr_count($classCode, 'function multipleTypeHints(int $a, float $b, bool $c, string $d)'));
        $this->assertEquals(1, substr_count($classCode, 'function combinationOfTypeHintsAndNormal(\stdClass $a, \Countable $b, $c, int $d)'));
        $this->assertEquals(1, substr_count($classCode, 'function typeHintsWithVariadic(int ...$foo)'));
        $this->assertEquals(1, substr_count($classCode, 'function withDefaultValue(int $foo = 123)'));
        $this->assertEquals(1, substr_count($classCode, 'function withDefaultValueNull(int $foo = NULL)'));
    }

    /**
     * @requires PHP 7.0
     */
    public function testClassWithReturnTypesOnProxiedMethods()
    {
        $className = ReturnTypesClass::class;
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\ReturnTypesClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyReturnTypesClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function returnsClass(): \stdClass'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsScalar(): int'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsArray(): array'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsCallable(): callable'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsSelf(): \\' . $className));
        $this->assertEquals(1, substr_count($classCode, 'function returnsParent(): \stdClass'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsInterface(): \Countable'));
    }

    /**
     * @requires PHP 7.1
     */
    public function testClassWithNullableTypeHintsOnProxiedMethods()
    {
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableTypeHintsClass', false)) {
            $className = NullableTypeHintsClass::class;
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableTypeHintsClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function nullableTypeHintInt(?int $param)'));
        $this->assertEquals(1, substr_count($classCode, 'function nullableTypeHintObject(?\stdClass $param)'));
        $this->assertEquals(1, substr_count($classCode, 'function nullableTypeHintSelf(?\\' . $className . ' $param)'));
        $this->assertEquals(1, substr_count($classCode, 'function nullableTypeHintWithDefault(?int $param = 123)'));
        $this->assertEquals(1, substr_count($classCode, 'function nullableTypeHintWithDefaultNull(int $param = NULL)'));
        $this->assertEquals(1, substr_count($classCode, 'function notNullableTypeHintWithDefaultNull(int $param = NULL)'));
    }

    /**
     * @requires PHP 7.1
     */
    public function testClassWithNullableReturnTypesOnProxiedMethods()
    {
        $className = NullableTypeHintsClass::class;
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableTypeHintsClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableTypeHintsClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function returnsNullableInt(): ?int'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsNullableObject(): ?\stdClass'));
        $this->assertEquals(1, substr_count($classCode, 'function returnsNullableSelf(): ?\\' . $className));
    }

    /**
     * @group #751
     *
     * @requires PHP 7.0
     */
    public function testClassWithNullableOptionalNonLastParameterOnProxiedMethods()
    {
        $className = NullableNonOptionalHintClass::class;

        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\NullableNonOptionalHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $this->assertContains(
            'public function midSignatureNullableParameter(\stdClass $param = NULL, $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableNonOptionalHintClass.php')
        );

        $this->assertContains(
            'public function midSignatureNotNullableHintedParameter(string $param = \'foo\', $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyNullableNonOptionalHintClass.php')
        );
    }

    /**
     * @group #751
     *
     * @requires PHP 7.1
     */
    public function testClassWithPhp71NullableOptionalNonLastParameterOnProxiedMethods()
    {
        $className = Php71NullableDefaultedNonOptionalHintClass::class;

        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\Php71NullableDefaultedNonOptionalHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $this->assertContains(
            'public function midSignatureNullableParameter(string $param = NULL, $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp71NullableDefaultedNonOptionalHintClass.php'),
            'Signature allows nullable type, although explicit "?" marker isn\'t used in the proxy'
        );

        $this->assertContains(
            'public function midSignatureNotNullableHintedParameter(?string $param = \'foo\', $secondParam)',
            file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyPhp71NullableDefaultedNonOptionalHintClass.php')
        );
    }

    /**
     * @requires PHP 7.1
     */
    public function testClassWithVoidReturnType()
    {
        $className = VoidReturnTypeClass::class;
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\VoidReturnTypeClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyVoidReturnTypeClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function returnsVoid(): void'));
    }

    /**
     * @requires PHP 7.0
     */
    public function testClassWithIterableTypeHint()
    {
        if (PHP_VERSION_ID < 70100) {
            $this->expectException(UnexpectedValueException::class);
        }

        $className = IterableTypeHintClass::class;
        if (!class_exists('Doctrine\Tests\Common\ProxyProxy\__CG__\IterableTypeHintClass', false)) {
            $metadata = $this->createClassMetadata($className, ['id']);

            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
            $this->generateAndRequire($proxyGenerator, $metadata);
        }

        $classCode = file_get_contents(__DIR__ . '/generated/__CG__DoctrineTestsCommonProxyIterableTypeHintClass.php');

        $this->assertEquals(1, substr_count($classCode, 'function parameterType(iterable $param)'));
        $this->assertEquals(1, substr_count($classCode, 'function returnType(): iterable'));
    }

    public function testClassWithInvalidTypeHintOnProxiedMethod()
    {
        $className = InvalidTypeHintClass::class;
        $metadata = $this->createClassMetadata($className, ['id']);
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'The type hint of parameter "foo" in method "invalidTypeHintMethod"'
                .' in class "' . $className . '" is invalid.'
        );
        $proxyGenerator->generateProxyClass($metadata);
    }

    /**
     * @requires PHP 7.0
     */
    public function testClassWithInvalidReturnTypeOnProxiedMethod()
    {
        $className = InvalidReturnTypeClass::class;
        $metadata = $this->createClassMetadata($className, ['id']);
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'The return type of method "invalidReturnTypeMethod"'
                .' in class "' . $className . '" is invalid.'
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
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);

        $proxyGenerator->generateProxyClass($this->createClassMetadata(EvalBase::class, ['id']));

        $reflClass = new ReflectionClass('Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\EvalBase');

        $this->assertContains("eval()'d code", $reflClass->getFileName());
    }

    /**
     * @param       $className
     * @param array $ids
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ClassMetadata
     */
    private function createClassMetadata($className, array $ids)
    {
        $metadata = $this->createMock(ClassMetadata::class);
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
