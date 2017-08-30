<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class FileDriverTest extends DoctrineTestCase
{
    public function testGlobalBasename()
    {
        $driver = new TestFileDriver([]);

        self::assertNull($driver->getGlobalBasename());

        $driver->setGlobalBasename("global");
        self::assertEquals("global", $driver->getGlobalBasename());
    }

    public function testGetElementFromGlobalFile()
    {
        $driver = new TestFileDriver($this->newLocator());
        $driver->setGlobalBasename("global");

        $element = $driver->getElement('stdGlobal');

        self::assertEquals('stdGlobal', $element);
    }

    public function testGetElementFromFile()
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('findMappingFile')
                ->with($this->equalTo('stdClass'))
                ->will($this->returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = new TestFileDriver($locator);

        self::assertEquals('stdClass', $driver->getElement('stdClass'));
    }

    public function testGetElementUpdatesClassCache()
    {
        $locator = $this->newLocator();

        // findMappingFile should only be called once
        $locator->expects($this->once())
            ->method('findMappingFile')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue(__DIR__ . '/_files/stdClass.yml'));

        $driver = new TestFileDriver($locator);

        // not cached
        self::assertEquals('stdClass', $driver->getElement('stdClass'));

        // cached call
        self::assertEquals('stdClass', $driver->getElement('stdClass'));
    }

    public function testGetAllClassNamesGlobalBasename()
    {
        $driver = new TestFileDriver($this->newLocator());
        $driver->setGlobalBasename("global");

        $classNames = $driver->getAllClassNames();

        self::assertEquals(['stdGlobal', 'stdGlobal2'], $classNames);
    }

    public function testGetAllClassNamesFromMappingFile()
    {
        $locator = $this->newLocator();
        $locator->expects($this->any())
                ->method('getAllClassNames')
                ->with($this->equalTo(null))
                ->will($this->returnValue(['stdClass']));
        $driver = new TestFileDriver($locator);

        $classNames = $driver->getAllClassNames();

        self::assertEquals(['stdClass'], $classNames);
    }

    public function testGetAllClassNamesBothSources()
    {
        $locator = $this->newLocator();
        $locator->expects($this->any())
                ->method('getAllClassNames')
                ->with($this->equalTo('global'))
                ->will($this->returnValue(['stdClass']));
        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename("global");

        $classNames = $driver->getAllClassNames();

        self::assertEquals(['stdGlobal', 'stdGlobal2', 'stdClass'], $classNames);
    }

    public function testIsNotTransient()
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('fileExists')
                ->with($this->equalTo('stdClass'))
                ->will($this->returnValue(true));

        $driver = new TestFileDriver($locator);
        $driver->setGlobalBasename("global");

        self::assertFalse($driver->isTransient('stdClass'));
        self::assertFalse($driver->isTransient('stdGlobal'));
        self::assertFalse($driver->isTransient('stdGlobal2'));
    }

    public function testIsTransient()
    {
        $locator = $this->newLocator();
        $locator->expects($this->once())
                ->method('fileExists')
                ->with($this->equalTo('stdClass2'))
                ->will($this->returnValue(false));

        $driver = new TestFileDriver($locator);

        self::assertTrue($driver->isTransient('stdClass2'));
    }

    public function testNonLocatorFallback()
    {
        $driver = new TestFileDriver(__DIR__ . '/_files', '.yml');
        self::assertTrue($driver->isTransient('stdClass2'));
        self::assertFalse($driver->isTransient('stdClass'));
    }

    private function newLocator()
    {
        $locator = $this->createMock(FileLocator::class);
        $locator->expects($this->any())->method('getFileExtension')->will($this->returnValue('.yml'));
        $locator->expects($this->any())->method('getPaths')->will($this->returnValue([__DIR__ . "/_files"]));
        return $locator;
    }
}

class TestFileDriver extends FileDriver
{
    protected function loadMappingFile($file)
    {
        if (strpos($file, "global.yml") !== false) {
            return ['stdGlobal' => 'stdGlobal', 'stdGlobal2' => 'stdGlobal2'];
        }
        return ['stdClass' => 'stdClass'];
    }

    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
    }
}
