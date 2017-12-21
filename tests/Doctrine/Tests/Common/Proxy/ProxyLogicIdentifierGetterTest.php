<?php
namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\ProxyGenerator;
use stdClass;

/**
 * Test that identifier getter does not cause lazy loading.
 * These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Jan Langer <jan.langer@slevomat.cz>
 */
class ProxyLogicIdentifierGetterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider methodsForWhichLazyLoadingShouldBeDisabled
     *
     * @param ClassMetadata $metadata
     * @param string        $fieldName
     * @param mixed         $expectedReturnedValue
     */
    public function testNoLazyLoadingForIdentifier(ClassMetadata $metadata, $fieldName, $expectedReturnedValue)
    {
        $className      = $metadata->getName();
        $proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\\' . $className;
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
        $proxyFileName  = $proxyGenerator->getProxyFileName($className);

        if ( ! class_exists($proxyClassName, false)) {
            $proxyGenerator->generateProxyClass($metadata, $proxyFileName);

            /** @noinspection PhpIncludeInspection */
            require_once $proxyFileName;
        }

        $proxy = new $proxyClassName(
            function () {
                self::fail('Initialization is never supposed to happen');
            },
            function () {
                self::fail('Initialization is never supposed to happen');
            }
        );

        $reflection = $metadata->getReflectionClass()->getProperty($fieldName);

        $reflection->setAccessible(true);
        $reflection->setValue($proxy, $expectedReturnedValue);

        self::assertSame($expectedReturnedValue, $proxy->{'get' . $fieldName}());
    }

    /**
     * @return array
     */
    public function methodsForWhichLazyLoadingShouldBeDisabled()
    {
        $methods = [
            [new LazyLoadableObjectClassMetadata(), 'protectedIdentifierField', 'foo'],
        ];

        if ( ! class_exists(\ReflectionType::class, false)) {
            return $methods;
        }

        $methods = array_merge(
            $methods,
            [
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldNoReturnTypehint', 'noTypeHint'],
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnTypehintScalar', 'scalarValue'],
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassFullyQualified', new LazyLoadableObjectWithTypehints()],
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassPartialUse', new LazyLoadableObjectWithTypehints()],
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassFullUse', new LazyLoadableObjectWithTypehints()],
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassOneWord', new stdClass()],
                [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetter', new stdClass()],
            ]
        );

        return array_merge(
            $methods,
            [
                [new LazyLoadableObjectWithNullableTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetterNullable', new stdClass()],
                [new LazyLoadableObjectWithNullableTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetterNullableWithSpace', new stdClass()],
            ]
        );
    }
}
