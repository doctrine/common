<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Entity;
use Doctrine\SubDirTestClass;
use Doctrine\SymlinkedTestClass;
use Doctrine\TestClass;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAllClassNames()
    {
        $this->makeSubDirSymlink();

        $reader = new AnnotationReader();
        $driver = new SimpleAnnotationDriver($reader, [__DIR__ . '/_files/annotation']);

        $classes = $driver->getAllClassNames();

        $expectedClasses = [SubDirTestClass::class, SymlinkedTestClass::class, TestClass::class];
        sort($expectedClasses);
        sort($classes);

        $this->assertEquals($expectedClasses, $classes);
    }

    private function makeSubDirSymlink()
    {
        $symlinkTarget = __DIR__ . '/_files/SubDirForSymlink';
        $symlinkPath = __DIR__ . '/_files/annotation/SubDirForSymlink';
        if (! is_link($symlinkPath)) {
            if (file_exists($symlinkPath)) {
                throw new \Exception(sprintf('Remove file/dir "%s".', $symlinkPath));
            }
            symlink($symlinkTarget, $symlinkPath);
        }
    }
}

class SimpleAnnotationDriver extends AnnotationDriver
{
    protected $entityAnnotationClasses = [Entity::class => true];

    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
    }
}
