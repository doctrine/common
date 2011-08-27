<?php

namespace Doctrine\Tests\Common\DateTime;

use Doctrine\Common\DateTime\DateTimeFactory;
use Doctrine\Common\DateTime\DateTime;

/**
 * @group DCOM-67
 */
class DateTimeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testNow()
    {
        $now1 = DateTimeFactory::now();
        $now2 = DateTimeFactory::now();

        $this->assertSame($now1, $now2);
    }

    public function testSetTestingNow()
    {
        $now = new DateTime("2010-10-10");
        DateTimeFactory::setTestingNow($now);

        $this->assertSame($now, DateTimeFactory::now());
    }

    public function testCreateFromFormat()
    {
        $date1 = DateTimeFactory::createFromFormat('!Y-m-d', '2010-10-10');
        $date2 = DateTimeFactory::createFromFormat('!Y-m-d', '2010-10-10');

        $this->assertSame($date1, $date2);

        $this->assertEquals('2010-10-10', $date1->format('Y-m-d'));

        $date3 = DateTimeFactory::createFromFormat('!Y-m-d', '2010-10-09');

        $this->assertNotSame($date1, $date3);
    }
}