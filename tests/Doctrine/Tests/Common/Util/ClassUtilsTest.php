<?php

namespace Doctrine\Tests\Common\Util

{
    use Doctrine\Tests\DoctrineTestCase;
    use Doctrine\Common\Util\ClassUtils;

    class ClassUtilsTest extends DoctrineTestCase
    {
        public static function dataGetClass()
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
        public function testGetRealClass($className, $expectedClassName)
        {
            self::assertEquals($expectedClassName, ClassUtils::getRealClass($className));
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testGetClass($className, $expectedClassName)
        {
            $object = new $className();
            self::assertEquals($expectedClassName, ClassUtils::getClass($object));
        }

        public function testGetParentClass()
        {
            $parentClass = ClassUtils::getParentClass('MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\Doctrine\Tests\Common\Util\ChildObject');
            self::assertEquals('stdClass', $parentClass);
        }

        public function testGenerateProxyClassName()
        {
            self::assertEquals('Proxies\__CG__\stdClass', ClassUtils::generateProxyClassName('stdClass', 'Proxies'));
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testNewReflectionClass($className, $expectedClassName)
        {
            $reflClass = ClassUtils::newReflectionClass($className);
            self::assertEquals($expectedClassName, $reflClass->getName());
        }

        /**
         * @dataProvider dataGetClass
         */
        public function testNewReflectionObject($className, $expectedClassName)
        {
            $object    = new $className;
            $reflClass = ClassUtils::newReflectionObject($object);
            self::assertEquals($expectedClassName, $reflClass->getName());
        }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
        public function testExistingClassNameWithColonIsNotDetectedAsAlias()
        {
            class_alias(self::class, 'a:b');
            self::assertFalse(ClassUtils::isClassNameAliasedClassName('a:b'));
        }

        public function testNonExistingClassNameWithColonIsDetectedAsAlias()
        {
            self::assertTrue(ClassUtils::isClassNameAliasedClassName('a:b'));
        }

        public function testClassNameWithoutColonIsNotDetectedAsAlias()
        {
            self::assertFalse(ClassUtils::isClassNameAliasedClassName('ab'));
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
