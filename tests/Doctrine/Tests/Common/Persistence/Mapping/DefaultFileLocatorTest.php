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
        self::assertEquals([$path], $locator->getPaths());

        $locator = new DefaultFileLocator($path);
        self::assertEquals([$path], $locator->getPaths());
    }

    public function testGetFileExtension()
    {
        $locator = new DefaultFileLocator([], ".yml");
        self::assertEquals(".yml", $locator->getFileExtension());
        $locator->setFileExtension(".xml");
        self::assertEquals(".xml", $locator->getFileExtension());
    }

    public function testUniquePaths()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path, $path]);
        self::assertEquals([$path], $locator->getPaths());
    }

    public function testFindMappingFile()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".yml");

        self::assertEquals(__DIR__ . '/_files' . DIRECTORY_SEPARATOR . 'stdClass.yml', $locator->findMappingFile('stdClass'));
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

        self::assertEquals($expectedAllClasses, $allClasses);
        self::assertEquals($expectedGlobalClasses, $globalClasses);
    }

    public function testGetAllClassNamesNonMatchingFileExtension()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".xml");
        self::assertEquals([], $locator->getAllClassNames("global"));
    }

    public function testFileExists()
    {
        $path = __DIR__ . "/_files";

        $locator = new DefaultFileLocator([$path], ".yml");

        self::assertTrue($locator->fileExists("stdClass"));
        self::assertFalse($locator->fileExists("stdClass2"));
        self::assertTrue($locator->fileExists("global"));
        self::assertFalse($locator->fileExists("global2"));
    }
}
