<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Util;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Util\Debug;

class DebugTest extends DoctrineTestCase
{
    public function testExportObject(): void
    {
        $obj = new \stdClass;
        $obj->foo = "bar";
        $obj->bar = 1234;

        $var = Debug::export($obj, 2);
        $this->assertEquals( "stdClass", $var->__CLASS__ );
    }

    public function testExportObjectWithReference(): void
    {
        $foo = 'bar';
        $bar = ['foo' => & $foo];
        $baz = (object) $bar;

        $var = Debug::export($baz, 2);
        $baz->foo = 'tab';

        $this->assertEquals('bar', $var->foo);
        $this->assertEquals('tab', $bar['foo']);
    }

    public function testExportArray(): void
    {
        $array = ['a' => 'b', 'b' => ['c', 'd' => ['e', 'f']]];
        $var = Debug::export($array, 2);
        $expected = $array;
        $expected['b']['d'] = 'Array(2)';
        $this->assertEquals($expected, $var);
    }

    public function testExportDateTime(): void
    {
        $obj = new \DateTime('2010-10-10 10:10:10', new \DateTimeZone('UTC'));

        $var = Debug::export($obj, 2);
        $this->assertEquals('DateTime', $var->__CLASS__);
        $this->assertEquals('2010-10-10T10:10:10+00:00', $var->date);
    }

    public function testExportDateTimeImmutable(): void
    {
        $obj = new \DateTimeImmutable('2010-10-10 10:10:10', new \DateTimeZone('UTC'));

        $var = Debug::export($obj, 2);
        $this->assertEquals('DateTimeImmutable', $var->__CLASS__);
        $this->assertEquals('2010-10-10T10:10:10+00:00', $var->date);
    }

    public function testExportDateTimeZone(): void
    {
        $obj = new \DateTimeImmutable('2010-10-10 12:34:56', new \DateTimeZone('Europe/Rome'));

        $var = Debug::export($obj, 2);
        $this->assertEquals('DateTimeImmutable', $var->__CLASS__);
        $this->assertEquals('2010-10-10T12:34:56+02:00', $var->date);
    }

    public function testExportArrayTraversable(): void
    {
        $obj = new \ArrayObject(['foobar']);

        $var = Debug::export($obj, 2);
        $this->assertContains('foobar', $var->__STORAGE__);

        $it = new \ArrayIterator(['foobar']);

        $var = Debug::export($it, 5);
        $this->assertContains('foobar', $var->__STORAGE__);
    }

    public function testReturnsOutput(): void
    {
        ob_start();

        $dump        = Debug::dump('foo');
        $outputValue = ob_get_contents();

        ob_end_clean();

        $this->assertSame($outputValue, $dump);
    }

    public function testDisablesOutput(): void
    {
        ob_start();

        $dump        = Debug::dump('foo', 2, true, false);
        $outputValue = ob_get_contents();

        ob_end_clean();

        $this->assertEmpty($outputValue);
        $this->assertNotSame($outputValue, $dump);
    }

    /**
     * @dataProvider provideAttributesCases
     */
    public function testExportParentAttributes(TestAsset\ParentClass $class, array $expected): void
    {
        $print_r_class = print_r($class, true);
        $print_r_expected = print_r($expected, true);

        $print_r_class = substr($print_r_class, strpos($print_r_class, '('));
        $print_r_expected = substr($print_r_expected, strpos($print_r_expected, '('));

        $this->assertSame($print_r_class, $print_r_expected);

        $var = Debug::export($class, 3);
        $var = (array) $var;
        unset($var['__CLASS__']);

        $this->assertSame($expected, $var);
    }

    public function provideAttributesCases(): array
    {
        return array(
            'different-attributes' => array(
                new TestAsset\ChildClass(),
                array(
                    'childPublicAttribute' => 4,
                    'childProtectedAttribute:protected' => 5,
                    'childPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ChildClass:private' => 6,
                    'parentPublicAttribute' => 1,
                    'parentProtectedAttribute:protected' => 2,
                    'parentPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ParentClass:private' => 3,
                ),
            ),
            'same-attributes' => array(
                new TestAsset\ChildWithSameAttributesClass(),
                array(
                    'parentPublicAttribute' => 4,
                    'parentProtectedAttribute:protected' => 5,
                    'parentPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ChildWithSameAttributesClass:private' => 6,
                    'parentPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ParentClass:private' => 3,
                ),
            ),
        );
    }
}
