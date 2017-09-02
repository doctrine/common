<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Entity;
use Doctrine\TestClass;

class AnnotationDriverTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAllClassNames()
    {
        $reader = new AnnotationReader();
        $driver = new SimpleAnnotationDriver($reader, [__DIR__ . '/_files/annotation']);

        $classes = $driver->getAllClassNames();

        self::assertEquals([TestClass::class], $classes);
    }
}

class SimpleAnnotationDriver extends AnnotationDriver
{
    protected $entityAnnotationClasses = [Entity::class => true];

    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
    }
}
