<?php
namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyGenerator;
use stdClass;

/**
 * Test that identifier getter does not cause lazy loading. These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Jan Langer <jan.langer@slevomat.cz>
 */
class ProxyLogicVoidReturnTypeTest extends \PHPUnit\Framework\TestCase
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
     * @var LazyLoadableObjectWithVoid|Proxy
     */
    protected $lazyObject;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Callable
     */
    protected $initializerCallbackMock;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->proxyLoader                = $loader      = $this->getMockBuilder(stdClass::class)->setMethods(['load'])->getMock();
        $this->initializerCallbackMock    = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $this->lazyLoadableObjectMetadata = $metadata = new LazyLoadableObjectWithVoidClassMetadata();

        $proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\LazyLoadableObjectWithVoid';

        // creating the proxy class
        if ( ! class_exists($proxyClassName, false)) {
            $proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . 'Proxy');
            $proxyFileName  = $proxyGenerator->getProxyFileName($metadata->getName());
            $proxyGenerator->generateProxyClass($metadata, $proxyFileName);
            require_once $proxyFileName;
        }

        $this->lazyObject = new $proxyClassName($this->getClosure($this->initializerCallbackMock));

        self::assertFalse($this->lazyObject->__isInitialized());
    }

    public function testParentVoidMethodIsCalledWithoutParameters()
    {
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, 'incrementingAndReturningVoid', []],
            function () {
            }
        );

        self::assertNull($this->lazyObject->incrementingAndReturningVoid());
        self::assertSame(1, $this->lazyObject->value);
    }

    public function testParentVoidMethodIsCalledWithParameters()
    {
        $this->configureInitializerMock(
            1,
            [$this->lazyObject, 'addingAndReturningVoid', [10]],
            function () {
            }
        );

        self::assertNull($this->lazyObject->addingAndReturningVoid(10));
        self::assertSame(10, $this->lazyObject->value);
    }

    /**
     * Converts a given callable into a closure
     *
     * @param  callable $callable
     * @return \Closure
     */
    private function getClosure($callable)
    {
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function configureInitializerMock(
        $expectedCallCount = 0,
        array $callParamsMatch = null,
        \Closure $callbackClosure = null
    ) {
        if ( ! $expectedCallCount) {
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
