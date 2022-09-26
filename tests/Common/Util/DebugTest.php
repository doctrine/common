<?php

namespace Doctrine\Tests\Common\Util;

use ArrayIterator;
use ArrayObject;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Util\Debug;
use Doctrine\Tests\DoctrineTestCase;
use stdClass;

use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function print_r;
use function strpos;
use function substr;

class DebugTest extends DoctrineTestCase
{
    public function testExportObject()
    {
        $obj      = new stdClass();
        $obj->foo = 'bar';
        $obj->bar = 1234;

        $var = Debug::export($obj, 2);
        self::assertEquals('stdClass', $var->__CLASS__);
    }

    public function testExportObjectWithReference()
    {
        $foo = 'bar';
        $bar = ['foo' => & $foo];
        $baz = (object) $bar;

        $var      = Debug::export($baz, 2);
        $baz->foo = 'tab';

        self::assertEquals('bar', $var->foo);
        self::assertEquals('tab', $bar['foo']);
    }

    public function testExportArray()
    {
        $array              = ['a' => 'b', 'b' => ['c', 'd' => ['e', 'f']]];
        $var                = Debug::export($array, 2);
        $expected           = $array;
        $expected['b']['d'] = 'Array(2)';
        self::assertEquals($expected, $var);
    }

    public function testExportDateTime()
    {
        $obj = new DateTime('2010-10-10 10:10:10', new DateTimeZone('UTC'));

        $var = Debug::export($obj, 2);
        self::assertEquals('DateTime', $var->__CLASS__);
        self::assertEquals('2010-10-10T10:10:10+00:00', $var->date);
    }

    public function testExportDateTimeImmutable()
    {
        $obj = new DateTimeImmutable('2010-10-10 10:10:10', new DateTimeZone('UTC'));

        $var = Debug::export($obj, 2);
        self::assertEquals('DateTimeImmutable', $var->__CLASS__);
        self::assertEquals('2010-10-10T10:10:10+00:00', $var->date);
    }

    public function testExportDateTimeZone()
    {
        $obj = new DateTimeImmutable('2010-10-10 12:34:56', new DateTimeZone('Europe/Rome'));

        $var = Debug::export($obj, 2);
        self::assertEquals('DateTimeImmutable', $var->__CLASS__);
        self::assertEquals('2010-10-10T12:34:56+02:00', $var->date);
    }

    public function testExportArrayTraversable()
    {
        $obj = new ArrayObject(['foobar']);

        $var = Debug::export($obj, 2);
        self::assertContains('foobar', $var->__STORAGE__);

        $it = new ArrayIterator(['foobar']);

        $var = Debug::export($it, 5);
        self::assertContains('foobar', $var->__STORAGE__);
    }

    public function testReturnsOutput()
    {
        ob_start();

        $dump        = Debug::dump('foo');
        $outputValue = ob_get_contents();

        ob_end_clean();

        self::assertSame($outputValue, $dump);
    }

    public function testDisablesOutput()
    {
        ob_start();

        $dump        = Debug::dump('foo', 2, true, false);
        $outputValue = ob_get_contents();

        ob_end_clean();

        self::assertEmpty($outputValue);
        self::assertNotSame($outputValue, $dump);
    }

    /**
     * @param array<string, int> $expected
     *
     * @dataProvider provideAttributesCases
     *
     * @requires PHP < 8.1.0
     */
    public function testExportParentAttributes(TestAsset\ParentClass $class, array $expected)
    {
        $actualRepresentation   = print_r($class, true);
        $expectedRepresentation = print_r($expected, true);

        $actualRepresentation   = substr($actualRepresentation, strpos($actualRepresentation, '('));
        $expectedRepresentation = substr($expectedRepresentation, strpos($expectedRepresentation, '('));

        self::assertSame($actualRepresentation, $expectedRepresentation);

        $var = Debug::export($class, 3);
        $var = (array) $var;
        unset($var['__CLASS__']);

        self::assertSame($expected, $var);
    }

    /**
     * @psalm-return array<string, array{TestAsset\ParentClass, mixed[]}>
     */
    public function provideAttributesCases()
    {
        return [
            'different-attributes' => [
                new TestAsset\ChildClass(),
                [
                    'childPublicAttribute' => 4,
                    'childProtectedAttribute:protected' => 5,
                    'childPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ChildClass:private' => 6,
                    'parentPublicAttribute' => 1,
                    'parentProtectedAttribute:protected' => 2,
                    'parentPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ParentClass:private' => 3,
                ],
            ],
            'same-attributes' => [
                new TestAsset\ChildWithSameAttributesClass(),
                [
                    'parentPublicAttribute' => 4,
                    'parentProtectedAttribute:protected' => 5,
                    'parentPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ChildWithSameAttributesClass:private' => 6,
                    'parentPrivateAttribute:Doctrine\Tests\Common\Util\TestAsset\ParentClass:private' => 3,
                ],
            ],
        ];
    }
}
