<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Util
{
    use Doctrine\Tests\DoctrineTestCase;
    use Doctrine\Common\Util\ClassUtils;

    class ClassUtilsTest extends DoctrineTestCase
    {
        static public function dataGetClass(): array
        {
            return [
                [\stdClass::class, \stdClass::class],
                [\Doctrine\Common\Util\ClassUtils::class, \Doctrine\Common\Util\ClassUtils::class],
                [ 'MyProject\Proxies\__CG__\stdClass', \stdClass::class],
                [ 'MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass', \stdClass::class],
                [ 'MyProject\Proxies\__CG__\Doctrine\Tests\Common\Util\ChildObject', ChildObject::class]
            ];
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testGetRealClass(string $className, string $expectedClassName): void
        {
            $this->assertEquals($expectedClassName, ClassUtils::getRealClass($className));
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testGetClass(string $className, string $expectedClassName): void
        {
            $object = new $className();
            $this->assertEquals($expectedClassName, ClassUtils::getClass($object));
        }

        public function testGetParentClass(): void
        {
            $parentClass = ClassUtils::getParentClass( 'MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\Doctrine\Tests\Common\Util\ChildObject' );
            $this->assertEquals('stdClass', $parentClass);
        }

        public function testGenerateProxyClassName(): void
        {
            $this->assertEquals( 'Proxies\__CG__\stdClass', ClassUtils::generateProxyClassName( 'stdClass', 'Proxies' ) );
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testNewReflectionClass(string $className, string $expectedClassName): void
        {
            $reflClass = ClassUtils::newReflectionClass( $className );
            $this->assertEquals( $expectedClassName, $reflClass->getName() );
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testNewReflectionObject(string $className, string $expectedClassName): void
        {
            $object = new $className;
            $reflClass = ClassUtils::newReflectionObject( $object );
            $this->assertEquals( $expectedClassName, $reflClass->getName() );
        }
    }

    class ChildObject extends \stdClass
    {
    }
}

namespace MyProject\Proxies\__CG__
{
    class stdClass extends \stdClass
    {
    }
}

namespace MyProject\Proxies\__CG__\Doctrine\Tests\Common\Util
{
    class ChildObject extends \Doctrine\Tests\Common\Util\ChildObject
    {
    }
}

namespace MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__
{
    class stdClass extends \MyProject\Proxies\__CG__\stdClass
    {
    }
}

namespace MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\Doctrine\Tests\Common\Util
{
    class ChildObject extends \MyProject\Proxies\__CG__\Doctrine\Tests\Common\Util\ChildObject
    {
    }
}
