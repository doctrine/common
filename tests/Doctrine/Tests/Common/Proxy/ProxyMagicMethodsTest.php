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
use Doctrine\Common\Proxy\Proxy;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Test for behavior of proxies with inherited magic methods
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ProxyMagicMethodsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Common\Proxy\ProxyGenerator
     */
    protected $proxyGenerator;

    /**
     * @var LazyLoadableObject|Proxy
     */
    protected $lazyObject;

    protected $identifier = [
        'publicIdentifierField' => 'publicIdentifierFieldValue',
        'protectedIdentifierField' => 'protectedIdentifierFieldValue',
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Callable
     */
    protected $initializerCallbackMock;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->proxyGenerator = new ProxyGenerator(__DIR__ . '/generated', __NAMESPACE__ . '\\MagicMethodProxy');
    }

    public static function tearDownAfterClass()
    {

    }

    public function testInheritedMagicGet()
    {
        $proxyClassName = $this->generateProxyClass(MagicGetClass::class);
        $proxy          = new $proxyClassName(
            function (Proxy $proxy, $method, $params) use (&$counter) {
                if ( ! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter            += 1;

                $proxy->__setInitializer($initializer);

            }
        );

        $this->assertSame('id', $proxy->id);
        $this->assertSame('modifiedPublicField', $proxy->publicField);
        $this->assertSame('test', $proxy->test);
        $this->assertSame('not defined', $proxy->notDefined);

        $this->assertSame(3, $counter);
    }

    /**
     * @group DCOM-194
     */
    public function testInheritedMagicGetByRef()
    {
        $proxyClassName    = $this->generateProxyClass(MagicGetByRefClass::class);
        /* @var $proxy \Doctrine\Tests\Common\Proxy\MagicGetByRefClass */
        $proxy             = new $proxyClassName();
        $proxy->valueField = 123;
        $value             = & $proxy->__get('value');

        $this->assertSame(123, $value);

        $value = 456;

        $this->assertSame(456, $proxy->__get('value'), 'Value was fetched by reference');

        $this->expectException(InvalidArgumentException::class);

        $undefined = $proxy->nonExisting;
    }

    public function testInheritedMagicSet()
    {
        $proxyClassName = $this->generateProxyClass(MagicSetClass::class);
        $proxy          = new $proxyClassName(
            function (Proxy  $proxy, $method, $params) use (&$counter) {
                if ( ! in_array($params[0], ['publicField', 'test', 'notDefined'])) {
                    throw new InvalidArgumentException('Unexpected access to field "' . $params[0] . '"');
                }

                $counter += 1;
            }
        );

        $this->assertSame('id', $proxy->id);

        $proxy->publicField = 'publicFieldValue';

        $this->assertSame('publicFieldValue', $proxy->publicField);

        $proxy->test = 'testValue';

        $this->assertSame('testValue', $proxy->testAttribute);

        $proxy->notDefined = 'not defined';

        $this->assertSame('not defined', $proxy->testAttribute);
        $this->assertSame(3, $counter);
    }

    public function testInheritedMagicSleep()
    {
        $proxyClassName = $this->generateProxyClass(MagicSleepClass::class);
        $proxy          = new $proxyClassName();

        $this->assertSame('defaultValue', $proxy->serializedField);
        $this->assertSame('defaultValue', $proxy->nonSerializedField);

        $proxy->serializedField    = 'changedValue';
        $proxy->nonSerializedField = 'changedValue';

        $unserialized = unserialize(serialize($proxy));

        $this->assertSame('changedValue', $unserialized->serializedField);
        $this->assertSame('defaultValue', $unserialized->nonSerializedField, 'Field was not returned by "__sleep"');
    }

    public function testInheritedMagicWakeup()
    {
        $proxyClassName = $this->generateProxyClass(MagicWakeupClass::class);
        $proxy          = new $proxyClassName();

        $this->assertSame('defaultValue', $proxy->wakeupValue);

        $proxy->wakeupValue = 'changedValue';
        $unserialized       = unserialize(serialize($proxy));

        $this->assertSame('newWakeupValue', $unserialized->wakeupValue, '"__wakeup" was called');

        $unserialized->__setInitializer(function (Proxy $proxy) {
            $proxy->__setInitializer(null);

            $proxy->publicField = 'newPublicFieldValue';
        });

        $this->assertSame('newPublicFieldValue', $unserialized->publicField, 'Proxy can still be initialized');
    }

    public function testInheritedMagicIsset()
    {
        $proxyClassName = $this->generateProxyClass(MagicIssetClass::class);
        $proxy          = new $proxyClassName(function (Proxy $proxy, $method, $params) use (&$counter) {
            if (in_array($params[0], ['publicField', 'test', 'nonExisting'])) {
                $initializer = $proxy->__getInitializer();

                $proxy->__setInitializer(null);

                $proxy->publicField = 'modifiedPublicField';
                $counter            += 1;

                $proxy->__setInitializer($initializer);

                return;
            }

            throw new InvalidArgumentException(
                sprintf('Should not be initialized when checking isset("%s")', $params[0])
            );
        });

        $this->assertTrue(isset($proxy->id));
        $this->assertTrue(isset($proxy->publicField));
        $this->assertTrue(isset($proxy->test));
        $this->assertFalse(isset($proxy->nonExisting));

        $this->assertSame(3, $counter);
    }

    public function testInheritedMagicClone()
    {
        $proxyClassName = $this->generateProxyClass(MagicCloneClass::class);
        $proxy          = new $proxyClassName(
            null,
            function ($proxy) {
                $proxy->cloned = true;
            }
        );

        $cloned = clone $proxy;

        $this->assertSame('newClonedValue', $cloned->clonedValue);
        $this->assertFalse($proxy->cloned);
        $this->assertTrue($cloned->cloned);
    }

    /**
     * @group DCOM-175
     */
    public function testClonesPrivateProperties()
    {
        $proxyClassName = $this->generateProxyClass(SerializedClass::class);
        /* @var $proxy SerializedClass */
        $proxy          = new $proxyClassName();

        $proxy->setFoo(1);
        $proxy->setBar(2);
        $proxy->setBaz(3);

        $unserialized = unserialize(serialize($proxy));

        $this->assertSame(1, $unserialized->getFoo());
        $this->assertSame(2, $unserialized->getBar());
        $this->assertSame(3, $unserialized->getBaz());
    }

    /**
     * @param $className
     *
     * @return string
     */
    private function generateProxyClass($className)
    {
        $proxyClassName = 'Doctrine\\Tests\\Common\\Proxy\\MagicMethodProxy\\__CG__\\' . $className;

        if (class_exists($proxyClassName, false)) {
            return $proxyClassName;
        }

        $metadata = $this->createMock(ClassMetadata::class);

        $metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($className));

        $metadata
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(['id']));

        $metadata
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new ReflectionClass($className)));

        $metadata
            ->expects($this->any())
            ->method('isIdentifier')
            ->will($this->returnCallback(function ($fieldName) {
                return 'id' === $fieldName;
            }));

        $metadata
            ->expects($this->any())
            ->method('hasField')
            ->will($this->returnCallback(function ($fieldName) {
                return in_array($fieldName, ['id', 'publicField']);
            }));

        $metadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnValue(false));

        $metadata
            ->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(['id', 'publicField']));

        $metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['id']));

        $metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));

        $metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->will($this->returnValue('string'));

        $this->proxyGenerator->generateProxyClass($metadata, $this->proxyGenerator->getProxyFileName($className));
        require_once $this->proxyGenerator->getProxyFileName($className);

        return $proxyClassName;
    }
}
