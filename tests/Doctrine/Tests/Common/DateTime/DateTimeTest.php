<?php

namespace Doctrine\Tests\Common\DateTime;

use Doctrine\Common\DateTime\DateTime;

/**
 * @group DCOM-67
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testAddImmutable()
    {
        $dateInterval = new \DateInterval('P2D');
        $a = new DateTime("2009-12-12");
        $b = $a->add($dateInterval);
        $this->assertNotSame($a, $b);

        $this->assertEquals('2009-12-12', $a->format('Y-m-d'));
        $this->assertEquals('2009-12-14', $b->format('Y-m-d'));
    }

    public function testSubImmutable()
    {
        $dateInterval = new \DateInterval('P2D');
        $a = new DateTime("2009-12-12");
        $b = $a->sub($dateInterval);
        $this->assertNotSame($a, $b);

        $this->assertEquals('2009-12-12', $a->format('Y-m-d'));
        $this->assertEquals('2009-12-10', $b->format('Y-m-d'));
    }

    public function testModifyImmutable()
    {
        $a = new DateTime("2009-12-12");
        $b = $a->modify('+2 days');
        $this->assertNotSame($a, $b);

        $this->assertEquals('2009-12-12', $a->format('Y-m-d'));
        $this->assertEquals('2009-12-14', $b->format('Y-m-d'));
    }

    public function testSetTimeZoneImmutable()
    {
        $a = new DateTime("2009-12-12");
        $b = $a->setTimezone(new \DateTimeZone("UTC"));

        $this->assertNotSame($a, $b);
    }

    public function testSetTimestamp()
    {
        $a = new DateTime("2009-12-12");

        $this->setExpectedException("Doctrine\Common\DateTime\ImmutableException");
        $a->setTimestamp(time());
    }

    public function testSetTime()
    {
        $a = new DateTime("2009-12-12");

        $this->setExpectedException("Doctrine\Common\DateTime\ImmutableException");
        $a->setTime(10, 20);
    }

    public function testSetDate()
    {
        $a = new DateTime("2009-12-12");

        $this->setExpectedException("Doctrine\Common\DateTime\ImmutableException");
        $a->setDate(2010, 10, 10);
    }

    public function testSetISODate()
    {
        $a = new DateTime("2009-12-12");

        $this->setExpectedException("Doctrine\Common\DateTime\ImmutableException");
        $a->setISODate(2010, 40);
    }

    /**
     * @group DDC-280
     */
    public function testComparable()
    {
        $a = new DateTime("2009-12-12", new \DateTimeZone("UTC"));
        $b = new DateTime("2009-12-12", new \DateTimeZone("UTC"));
        $c = new DateTime("2009-12-12", new \DateTimeZone("Europe/Berlin"));
        $d = new DateTime("2009-12-12", new \DateTimeZone("Europe/Zurich"));

        $this->assertTrue($a->equals($b), "Equal date");
        $this->assertFalse($a->equals("b"), "Handle non DateTime values");
        $this->assertFalse($a->equals($c), "Different Timezone");
        $this->assertFalse($a->equals($d), "Even though Berlin and Zurich is always the same time they are different dates.");
    }

    public function testToString()
    {
        $a = new DateTime("2009-12-12", new \DateTimeZone("UTC"));
        $this->assertEquals("2009-12-12 00:00:00.000000 UTC", (string)$a);
    }
}