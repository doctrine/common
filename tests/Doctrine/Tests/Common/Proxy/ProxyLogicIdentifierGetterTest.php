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
use Doctrine\Common\Proxy\ProxyGenerator;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Test that identifier getter does not cause lazy loading.
 * These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Jan Langer <jan.langer@slevomat.cz>
 */
class ProxyLogicIdentifierGetterTest extends PHPUnit_Framework_TestCase
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
        $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy', true);
        $proxyFileName  = $proxyGenerator->getProxyFileName($className);

        if (! class_exists($proxyClassName, false)) {
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

        $this->assertSame($expectedReturnedValue, $proxy->{'get' . $fieldName}());
    }

    /**
     * @return array
     */
    public function methodsForWhichLazyLoadingShouldBeDisabled()
    {
        $methods = [
            [new LazyLoadableObjectClassMetadata(), 'protectedIdentifierField', 'foo'],
        ];

        if (! class_exists(\ReflectionType::class, false)) {
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

        if (PHP_VERSION_ID < 70100) {
            return $methods;
        }

        return array_merge(
            $methods,
            [
                [new LazyLoadableObjectWithNullableTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetterNullable', new stdClass()],
                [new LazyLoadableObjectWithNullableTypehintsClassMetadata(), 'identifierFieldReturnClassOneLetterNullableWithSpace', new stdClass()],
            ]
        );
    }
}
