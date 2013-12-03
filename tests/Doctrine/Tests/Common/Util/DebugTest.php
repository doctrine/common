<?php

namespace Doctrine\Tests\Common\Util;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Util\Debug;

class DebugTest extends DoctrineTestCase
{
    public function testExportObject( )
    {
        $obj = new \stdClass;
        $obj->foo = "bar";
        $obj->bar = 1234;

        $var = Debug::export($obj, 2);
        $this->assertEquals( "stdClass", $var->__CLASS__ );
    }

    public function testExportDateTime()
    {
        $obj = new \DateTime( "2010-10-10 10:10:10" );

        $var = Debug::export( $obj, 2 );
        $this->assertEquals( "DateTime", $var->__CLASS__ );
    }

    public function testExportArrayTraversable()
    {
        $obj = new \ArrayObject(array('foobar'));

        $var = Debug::export($obj, 2);
        $this->assertContains('foobar', $var->__STORAGE__);

        $it = new \ArrayIterator(array('foobar'));

        $var = Debug::export($it, 5);
        $this->assertContains('foobar', $var->__STORAGE__);
    }

    public function testReturnsOutput()
    {
        ob_start();

        $dump        = Debug::dump('foo');
        $outputValue = ob_get_contents();

        ob_end_clean();

        $this->assertSame($outputValue, $dump);
    }

    public function testDisablesOutput()
    {
        ob_start();

        $dump        = Debug::dump('foo', 2, true, false);
        $outputValue = ob_get_contents();

        ob_end_clean();

        $this->assertEmpty($outputValue);
        $this->assertNotSame($outputValue, $dump);
    }
}
