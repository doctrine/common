<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class FileDriverTest extends DoctrineTestCase
{
    public function testGlobalBasename(): void
    {
        $driver = new TestFileDriver([]);

        $this->assertNull($driver->getGlobalBasename());

        $driver->setGlobalBasename("global");
        $this->assertEquals("global", $driver->getGlobalBasename());
    }

    public function testGetElementFromGlobalFile(): void
    {
        $driver = new TestFileDriver($this->newLocator());
        $driver->setGlobalBasename("global");

        $element = $driver->getElement('stdGlobal');

        $this->assertEquals('stdGlobal', $element);
    }

    public function testGetElementFromFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('findMappingFile')
                ->with($this->equalTo('stdClass'))
                ->will($this->returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = new TestFileDriver($locator);

        $this->assertEquals('stdClass', $driver->getElement('stdClass'));
    }

    public function testGetElementUpdatesClassCache(): void
    {
        $locator = $this->newLocator();

        // findMappingFile should only be called once
        $locator->expects($this->once())
            ->method('findMappingFile')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = new TestFileDriver($locator);

        // not cached
        $this->assertEquals('stdClass', $driver->getElement('stdClass'));

        // cached call
        $this->assertEquals('stdClass', $driver->getElement('stdClass'));
    }

    public function testGetAllClassNamesGlobalBasename(): void
    {
        $driver = new TestFileDriver($this->newLocator());
        $driver->setGlobalBasename("global");

        $classNames = $driver->getAllClassNames();

        $this->assertEquals(['stdGlobal', 'stdGlobal2'], $classNames);
    }

    public function testGetAllClassNamesFromMappingFile(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->any())
                ->method('getAllClassNames')
                ->with($this->equalTo(null))
                ->will($this->returnValue(['stdClass']));
        $driver = new TestFileDriver($locator);

        $classNames = $driver->getAllClassNames();

        $this->assertEquals(['stdClass'], $classNames);
    }

    public function testGetAllClassNamesBothSources(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->any())
                ->method('getAllClassNames')
                ->with($this->equalTo('global'))
                ->will($this->returnValue(['stdClass']));
        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename("global");

        $classNames = $driver->getAllClassNames();

        $this->assertEquals(['stdGlobal', 'stdGlobal2', 'stdClass'], $classNames);
    }

    public function testIsNotTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('fileExists')
                ->with($this->equalTo('stdClass'))
                ->will($this->returnValue( true ));

        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename("global");

        $this->assertFalse($driver->isTransient('stdClass'));
        $this->assertFalse($driver->isTransient('stdGlobal'));
        $this->assertFalse($driver->isTransient('stdGlobal2'));
    }

    public function testIsTransient(): void
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('fileExists')
                ->with($this->equalTo('stdClass2'))
                ->will($this->returnValue( false ));

        $driver = new TestFileDriver($locator);

        $this->assertTrue($driver->isTransient('stdClass2'));
    }

    public function testNonLocatorFallback(): void
    {
        $driver = new TestFileDriver(__DIR__ . '/_files', '.yml');
        $this->assertTrue($driver->isTransient('stdClass2'));
        $this->assertFalse($driver->isTransient('stdClass'));
    }

    /**
     * @return FileLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function newLocator(): FileLocator
    {
        $locator = $this->createMock(FileLocator::class);
        $locator->expects($this->any())->method('getFileExtension')->will($this->returnValue('.yml'));
        $locator->expects($this->any())->method('getPaths')->will($this->returnValue([__DIR__ . "/_files"]));
        return $locator;
    }
}

class TestFileDriver extends FileDriver
{
    protected function loadMappingFile(string $file): array
    {
        if (strpos($file, "global.yml") !== false) {
            return ['stdGlobal' => 'stdGlobal', 'stdGlobal2' => 'stdGlobal2'];
        }
        return ['stdClass' => 'stdClass'];
    }

    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {

    }
}
