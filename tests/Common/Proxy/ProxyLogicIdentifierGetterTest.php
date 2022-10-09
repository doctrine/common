<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use stdClass;
use function class_exists;

use const PHP_VERSION_ID;

/**
 * Test that identifier getter does not cause lazy loading.
 * These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 */
class ProxyLogicIdentifierGetterTest extends TestCase
{
    /**
     * @param string $fieldName
     * @param mixed  $expectedReturnedValue
     *
     * @dataProvider methodsForWhichLazyLoadingShouldBeDisabled
     */
    public function testNoLazyLoadingForIdentifier(ClassMetadata $metadata, $fieldName, $expectedReturnedValue)
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
        $reflection->setValue($proxy, $expectedReturnedValue);

        self::assertSame($expectedReturnedValue, $proxy->{'get' . $fieldName}());
    }

    /**
     * @return array<mixed[]>
     *
     * @psalm-return list<array{ClassMetadata,string,mixed}>
     */
    public function methodsForWhichLazyLoadingShouldBeDisabled()
    {
        $data = [
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
        ];

        if (PHP_VERSION_ID >= 80000) {
            $data[] = [new LazyLoadableObjectWithPHP8UnionTypeClassMetadata(), 'identifierFieldUnionType', 123];
            $data[] = [new LazyLoadableObjectWithPHP8UnionTypeClassMetadata(), 'identifierFieldUnionType', 'string'];
        }

        if (PHP_VERSION_ID >= 80100) {
            $data[] = [new LazyLoadableObjectWithPHP81IntersectionTypeClassMetadata(), 'identifierFieldIntersectionType', new class extends \stdClass implements \Stringable {
                public function __toString(): string
                {
                    return '';
                }
            }];
        }

        if (PHP_VERSION_ID >= 80200) {
            $data[] = [new LazyLoadableObjectWithPHP82UnionAndIntersectionTypeClassMetadata(), 'identifierFieldUnionAndIntersectionType', new class extends \stdClass implements \Stringable {
                public function __toString(): string
                {
                    return '';
                }
            }];
        }

        return $data;
    }
}
