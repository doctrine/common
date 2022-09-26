<?php

namespace Doctrine\Tests\Common\Util

{

    use Doctrine\Common\Util\ClassUtils;
    use Doctrine\Tests\DoctrineTestCase;

    class ClassUtilsTest extends DoctrineTestCase
    {
        /**
         * @psalm-return list<array{class-string, class-string}>
         */
        public static function dataGetClass()
        {
            return [
                [\stdClass::class, \stdClass::class],
                [ClassUtils::class, ClassUtils::class],
                [ 'MyProject\Proxies\__CG__\stdClass', \stdClass::class],
                [ 'MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass', \stdClass::class],
                [ 'MyProject\Proxies\__CG__\Doctrine\Tests\Common\Util\ChildObject', ChildObject::class],
            ];
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testGetRealClass(string $className, string $expectedClassName): void
        {
            self::assertEquals($expectedClassName, ClassUtils::getRealClass($className));
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testGetClass(string $className, string $expectedClassName): void
        {
            $object = new $className();
            self::assertEquals($expectedClassName, ClassUtils::getClass($object));
        }

        public function testGetParentClass(): void
        {
            $parentClass = ClassUtils::getParentClass('MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\Doctrine\Tests\Common\Util\ChildObject');
            self::assertEquals('stdClass', $parentClass);
        }

        public function testGenerateProxyClassName(): void
        {
            self::assertEquals('Proxies\__CG__\stdClass', ClassUtils::generateProxyClassName('stdClass', 'Proxies'));
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testNewReflectionClass(string $className, string $expectedClassName): void
        {
            $reflClass = ClassUtils::newReflectionClass($className);
            self::assertEquals($expectedClassName, $reflClass->getName());
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testNewReflectionObject(string $className, string $expectedClassName): void
        {
            $object    = new $className();
            $reflClass = ClassUtils::newReflectionObject($object);
            self::assertEquals($expectedClassName, $reflClass->getName());
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
