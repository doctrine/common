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

use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Test that identifier getter does not cause lazy loading. These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Jan Langer <jan.langer@slevomat.cz>
 */
class ProxyLogicTypehintsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $proxyLoader;

    /**
     * @var ClassMetadata
     */
    protected $lazyLoadableObjectMetadata;

    /**
     * @var LazyLoadableObjectWithTypehints|Proxy
     */
    protected $lazyObject;

    protected $identifier = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Callable
     */
    protected $initializerCallbackMock;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Return type hints are only supported in PHP >= 7.0.0.');
        }
        $this->identifier = [
            'identifierFieldNoReturnTypehint' => 'noTypeHint',
            'identifierFieldReturnTypehintScalar' => 'scalarValue',
            'identifierFieldReturnClassFullyQualified' => new LazyLoadableObjectWithTypehints(),
            'identifierFieldReturnClassPartialUse' => new LazyLoadableObjectWithTypehints(),
            'identifierFieldReturnClassFullUse' => new LazyLoadableObjectWithTypehints(),
            'identifierFieldReturnClassOneWord' => new stdClass(),
            'identifierFieldReturnClassOneLetter' => new stdClass(),
        ];

        $this->proxyLoader = $loader      = $this->getMockBuilder(stdClass::class)->setMethods(['load'])->getMock();
        $this->initializerCallbackMock    = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $identifier                       = $this->identifier;
        $this->lazyLoadableObjectMetadata = $metadata = new LazyLoadableObjectWithTypehintsClassMetadata();

        // emulating what should happen in a proxy factory
        $cloner = function (LazyLoadableObjectWithTypehints $proxy) use ($loader, $identifier, $metadata) {
            /* @var $proxy LazyLoadableObjectWithTypehints|Proxy */
            if ($proxy->__isInitialized()) {
                return;
            }

            $proxy->__setInitialized(true);
            $proxy->__setInitializer(null);
            $original = $loader->load($identifier);

            if (null === $original) {
                throw new UnexpectedValueException();
            }

            foreach ($metadata->getReflectionClass()->getProperties() as $reflProperty) {
                $propertyName = $reflProperty->getName();

                if ($metadata->hasField($propertyName) || $metadata->hasAssociation($propertyName)) {
                    $reflProperty->setAccessible(true);
                    $reflProperty->setValue($proxy, $reflProperty->getValue($original));
                }
            }
        };

        $proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\LazyLoadableObjectWithTypehints';

        // creating the proxy class
        if (!class_exists($proxyClassName, false)) {
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $proxyFileName = $proxyGenerator->getProxyFileName($metadata->getName());
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

        $this->assertFalse($this->lazyObject->__isInitialized());
    }

    /**
     * @dataProvider dataNoLazyLoadingForIdentifier
     * @param string $field
     */
    public function testNoLazyLoadingForIdentifier($field)
    {
        $this->configureInitializerMock(0);
        $getter = 'get' . ucfirst($field);

        $this->assertSame($this->identifier[$field], $this->lazyObject->$getter());
    }

    /**
     * @return array
     */
    public function dataNoLazyLoadingForIdentifier()
    {
        return [
            ['identifierFieldNoReturnTypehint'],
            ['identifierFieldReturnTypehintScalar'],
            ['identifierFieldReturnClassFullyQualified'],
            ['identifierFieldReturnClassPartialUse'],
            ['identifierFieldReturnClassFullUse'],
            ['identifierFieldReturnClassOneWord'],
            ['identifierFieldReturnClassOneLetter'],
        ];
    }


    /**
     * Converts a given callable into a closure
     *
     * @param  callable $callable
     * @return \Closure
     */
    private function getClosure($callable) {
        return function () use ($callable) {
            call_user_func_array($callable, func_get_args());
        };
    }

    /**
     * Configures the current initializer callback mock with provided matcher params
     *
     * @param int $expectedCallCount the number of invocations to be expected. If a value< 0 is provided, `any` is used
     * @param array $callParamsMatch an ordered array of parameters to be expected
     * @param callable $callbackClosure a return callback closure
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|
     */
    private function configureInitializerMock(
        $expectedCallCount = 0,
        array $callParamsMatch = null,
        \Closure $callbackClosure = null
    ) {
        if (!$expectedCallCount) {
            $invocationCountMatcher = $this->exactly((int) $expectedCallCount);
        } else {
            $invocationCountMatcher = $expectedCallCount < 0 ? $this->any() : $this->exactly($expectedCallCount);
        }

        $invocationMocker = $this->initializerCallbackMock->expects($invocationCountMatcher)->method('__invoke');

        if (null !== $callParamsMatch) {
            call_user_func_array([$invocationMocker, 'with'], $callParamsMatch);
        }

        if ($callbackClosure) {
            $invocationMocker->will($this->returnCallback($callbackClosure));
        }
    }

}
