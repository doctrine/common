<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;

class DefaultFileLocatorTest extends DoctrineTestCase
{
    public function testGetPaths()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path]);
        $this->assertEquals([$path], $locator->getPaths());

        $locator = new DefaultFileLocator($path);
        $this->assertEquals([$path], $locator->getPaths());
    }

    public function testGetFileExtension()
    {
        $locator = new DefaultFileLocator([], ".yml");
        $this->assertEquals(".yml", $locator->getFileExtension());
        $locator->setFileExtension(".xml");
        $this->assertEquals(".xml", $locator->getFileExtension());
    }

    public function testUniquePaths()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path, $path]);
        $this->assertEquals([$path], $locator->getPaths());
    }

    public function testFindMappingFile()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".yml");

        $this->assertEquals(__DIR__ . '/_files' . DIRECTORY_SEPARATOR . 'stdClass.yml', $locator->findMappingFile('stdClass'));
    }

    public function testFindMappingFileNotFound()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".yml");

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("No mapping file found named 'stdClass2.yml' for class 'stdClass2'");
        $locator->findMappingFile('stdClass2');
    }

    public function testGetAllClassNames()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".yml");
        $allClasses = $locator->getAllClassNames(null);
        $globalClasses = $locator->getAllClassNames("global");

        $expectedAllClasses    = ['global', 'stdClass', 'subDirClass'];
        $expectedGlobalClasses = ['subDirClass', 'stdClass'];

        sort($allClasses);
        sort($globalClasses);
        sort($expectedAllClasses);
        sort($expectedGlobalClasses);

        $this->assertEquals($expectedAllClasses, $allClasses);
        $this->assertEquals($expectedGlobalClasses, $globalClasses);
    }

    public function testGetAllClassNamesNonMatchingFileExtension()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".xml");
        $this->assertEquals([], $locator->getAllClassNames("global"));
    }

    public function testFileExists()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".yml");

        $this->assertTrue($locator->fileExists("stdClass"));
        $this->assertFalse($locator->fileExists("stdClass2"));
        $this->assertTrue($locator->fileExists("global"));
        $this->assertFalse($locator->fileExists("global2"));
    }
}
