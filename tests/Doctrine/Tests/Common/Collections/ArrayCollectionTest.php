<?php

namespace Doctrine\Tests\Common\Collections;

use Doctrine\Tests;
use Doctrine\Common\Collections\ArrayCollection;

class ArrayCollectionTest extends \Doctrine\Tests\DoctrineTestCase
{
    /**
     * @dataProvider isArrayCollectionDataprovider
     * @covers \\Doctrine\\Common\\Collections\\ArrayCollection::isArrayCollection
     */
    public function testIsCollection($expected, $c) 
    {
        $collection = new ArrayCollection();
        $this->assertEquals($expected, $collection->isArrayCollection($c));
    }

    /**
     * @covers \Doctrine\Common\Collections\ArrayCollection::merge
     */
    public function testMergeExpectingOnlyOneArgumentGiven() {
        $collection = new ArrayCollection();
        $this->assertSame($collection, $collection->merge($collection));
    }
  
    /**
     * @expectedException InvalidArgumentException
     * @covers \Doctrine\Common\Collections\ArrayCollection::merge
     */
    public function testMergeExpectingInvalidArgumentExceptionBecauseInvalidArgumentsGiven() {
        $collection = new ArrayCollection();
        $collection->merge(new ArrayCollection(), 'not a collection at all');
    }

    /**
     * @covers \Doctrine\Common\Collections\ArrayCollection::merge
     * @covers \Doctrine\Common\Collections\ArrayCollection::doMerge
     */
    public function testMerge() {
        $c1 = new ArrayCollection();
        $c1['mascott'] = 'tux';
        $c1[] = 'tux';
        $c2 = new ArrayCollection();
        $c2['mascott'] = 'Beastie';
        $c2[] = 'tux';

        $collection = new ArrayCollection();
        $c3 = $collection->merge($c1, $c2);

        $this->assertEquals('Beastie', $c3['mascott']);
        $this->assertCount(3, $c3);
    }

    /**
     * @covers \Doctrine\Common\Collections\ArrayCollection::merge
     * @covers \Doctrine\Common\Collections\ArrayCollection::doMerge
     */
    public function testMergeMoreThanTwoCollections() {
        $c1 = new ArrayCollection();
        $c1['mascott'] = 'tux';
        $c1[] = 'tux';
        $c2 = new ArrayCollection();
        $c2['mascott'] = 'Beastie';
        $c2[] = 'tux';
        $c3 = new ArrayCollection();
        $c3['os'] = 'Linux';
        $c3[] = 'gnu';

        $collection = new ArrayCollection();
        $c = $collection->merge($c1, $c2, $c3);

        $this->assertEquals('Beastie', $c['mascott']);
        $this->assertCount(5, $c);
    }


    public static function isArrayCollectionDataprovider() 
    {
        return array(
            'check an actual collection » true' => array(true, new ArrayCollection()),
            'check with s.th other than a collection » false' => array (false, 'not a collection at all'),
        );
    }
}
