<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;

class StaticPHPDriverTest extends DoctrineTestCase
{
    public function testLoadMetadata()
    {
        /* @var $metadata ClassMetadata|\PHPUnit_Framework_MockObject_MockObject */
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getFieldNames');

        $driver = new StaticPHPDriver([__DIR__]);
        $driver->loadMetadataForClass(TestEntity::class, $metadata);
    }

    public function testGetAllClassNames()
    {
        $driver = new StaticPHPDriver([__DIR__]);
        $classNames = $driver->getAllClassNames();

        $this->assertContains(TestEntity::class, $classNames);
    }
}

class TestEntity
{
    static public function loadMetadata($metadata)
    {
        $metadata->getFieldNames();
    }
}