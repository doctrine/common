<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAllClassNames()
    {
        $reader = new AnnotationReader();
        $driver = new SimpleAnnotationDriver($reader, array(__DIR__ . '/_files/annotation'));

        $classes = $driver->getAllClassNames();

        $this->assertEquals(array('Doctrine\TestClass'), $classes);
    }
}

class SimpleAnnotationDriver extends AnnotationDriver
{
    protected $entityAnnotationClasses = array('Doctrine\Entity' => true);

    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
    }
}
