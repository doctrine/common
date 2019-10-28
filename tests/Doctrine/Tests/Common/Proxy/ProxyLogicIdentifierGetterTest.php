<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use stdClass;
use function class_exists;

/**
 * Test that identifier getter does not cause lazy loading.
 * These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 */
class ProxyLogicIdentifierGetterTest extends TestCase
{
    /**
     * @param string $fieldName
     * @param mixed  $value
     * @param mixed         $expectedReturnedValue
     *
     * @dataProvider methodsForWhichLazyLoadingShouldBeDisabled
     */
    public function testNoLazyLoadingForIdentifier(ClassMetadata $metadata, $fieldName, $value, $expectedReturnedValue = null)
    {
        $className      = $metadata->getName();
        $proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\\' . $className;
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
        $proxyFileName  = $proxyGenerator->getProxyFileName($className);

        if (! class_exists($proxyClassName, false)) {
            $proxyGenerator->generateProxyClass($metadata, $proxyFileName);

            /** @noinspection PhpIncludeInspection */
            require_once $proxyFileName;
        }

        $proxy = new $proxyClassName(
            static function () {
                self::fail('Initialization is never supposed to happen');
            },
            static function () {
                self::fail('Initialization is never supposed to happen');
            }
        );

        $reflection = $metadata->getReflectionClass()->getProperty($fieldName);

        $reflection->setAccessible(true);
        $reflection->setValue($proxy, $value);

        if ($expectedReturnedValue === null) {
            self::assertSame($value, $proxy->{'get' . $fieldName}());
        } else {
            self::assertEquals($expectedReturnedValue, $proxy->{'get' . $fieldName}());
        }
    }

    /**
     * @return array<mixed[]>
     *
     * @psalm-return list<array{ClassMetadata,string,mixed}>
     */
    public function methodsForWhichLazyLoadingShouldBeDisabled()
    {
        return [
            [new LazyLoadableObjectClassMetadata(), 'protectedIdentifierField', 'foo'],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldNoReturnTypehint', 'noTypeHint'],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnTypehintScalar', 'scalarValue'],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassFullyQualified', new LazyLoadableObjectWithTypehints()],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassPartialUse', new LazyLoadableObjectWithTypehints()],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassFullUse', new LazyLoadableObjectWithTypehints()],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassOneWord', new stdClass()],
            [new LazyLoadableObjectWithTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetter', new stdClass()],
            [new LazyLoadableObjectWithTraitClassMetadata(), 'identifierFieldInTrait', 123],
            [new LazyLoadableObjectWithNullableTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetterNullable', new stdClass()],
            [new LazyLoadableObjectWithNullableTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetterNullableWithSpace', new stdClass()],
            [new LazyLoadableObjectWithCustomIdTypeClassMetadata(), 'identifierFieldWithStaticVOConstructor', 'a', ValueId::new('a')],
            [new LazyLoadableObjectWithCustomIdTypeClassMetadata(), 'identifierFieldWithVOConstructor', 'b', ValueId::new('b')],
        ];
    }
}
