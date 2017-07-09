<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;

class SymfonyFileLocatorTest extends DoctrineTestCase
{
    public function testGetPaths(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix]);
        $this->assertEquals([$path], $locator->getPaths());

        $locator = new SymfonyFileLocator([$path => $prefix]);
        $this->assertEquals([$path], $locator->getPaths());
    }

    public function testGetPrefixes(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix]);
        $this->assertEquals([$path => $prefix], $locator->getNamespacePrefixes());
    }

    public function testGetFileExtension(): void
    {
        $locator = new SymfonyFileLocator([], ".yml");
        $this->assertEquals(".yml", $locator->getFileExtension());
        $locator->setFileExtension(".xml");
        $this->assertEquals(".xml", $locator->getFileExtension());
    }

    public function testFileExists(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix], ".yml");

        $this->assertTrue($locator->fileExists("Foo\stdClass"));
        $this->assertTrue($locator->fileExists("Foo\global"));
        $this->assertFalse($locator->fileExists("Foo\stdClass2"));
        $this->assertFalse($locator->fileExists("Foo\global2"));
    }

    public function testGetAllClassNames(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix], ".yml");
        $allClasses = $locator->getAllClassNames(null);
        $globalClasses = $locator->getAllClassNames("global");

        $expectedAllClasses    = ["Foo\\Bar\\subDirClass", "Foo\\global", "Foo\\stdClass"];
        $expectedGlobalClasses = ["Foo\\Bar\\subDirClass", "Foo\\stdClass"];

        sort($allClasses);
        sort($globalClasses);
        sort($expectedAllClasses);
        sort($expectedGlobalClasses);

        $this->assertEquals($expectedAllClasses, $allClasses);
        $this->assertEquals($expectedGlobalClasses, $globalClasses);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Namespace separator should not be empty
     */
    public function testInvalidCustomNamespaceSeparator(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        new SymfonyFileLocator([$path => $prefix], ".yml", "");
    }

    public function customNamespaceSeparatorProvider(): array
    {
        return [
            'directory separator' => [DIRECTORY_SEPARATOR, "/_custom_ns/dir"],
            'default dot separator' => ['.', "/_custom_ns/dot"],
        ];
    }

    /**
     * @dataProvider customNamespaceSeparatorProvider
     *
     * @param $separator string Directory separator to test against
     * @param $dir       string Path to load mapping data from
     *
     * @throws MappingException
     */
    public function testGetClassNamesWithCustomNsSeparator(string $separator, string $dir): void
    {
        $path = __DIR__ . $dir;
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix], ".yml", $separator);
        $classes = $locator->getAllClassNames(null);
        sort($classes);

        $this->assertEquals(["Foo\\stdClass", "Foo\\sub\\subClass", "Foo\\sub\\subsub\\subSubClass"], $classes);
    }

    public function customNamespaceLookupQueryProvider(): array
    {
        return [
            'directory separator'  => [
                DIRECTORY_SEPARATOR,
                "/_custom_ns/dir",
                [
                    "stdClass.yml"               => "Foo\\stdClass",
                    "sub/subClass.yml"           => "Foo\\sub\\subClass",
                    "sub/subsub/subSubClass.yml" => "Foo\\sub\\subsub\\subSubClass",
                ]
            ],
            'default dot separator' => [
                '.',
                "/_custom_ns/dot",
                [
                    "stdClass.yml"               => "Foo\\stdClass",
                    "sub.subClass.yml"           => "Foo\\sub\\subClass",
                    "sub.subsub.subSubClass.yml" => "Foo\\sub\\subsub\\subSubClass",
                ]
            ],
        ];
    }

    /** @dataProvider customNamespaceLookupQueryProvider
     * @param $separator string Directory separator to test against
     * @param $dir       string Path to load mapping data from
     * @param $files     array  Files to lookup classnames
     *
     * @throws MappingException
     */
    public function testFindMappingFileWithCustomNsSeparator(string $separator, string $dir, array $files): void
    {
        $path   = __DIR__ . $dir;
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix], ".yml", $separator);

        foreach ($files as $filePath => $className) {
            $this->assertEquals(realpath($path .'/'. $filePath), realpath($locator->findMappingFile($className)));
        }

    }


    public function testFindMappingFile(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix], ".yml");

        $this->assertEquals(__DIR__ . "/_files/stdClass.yml", $locator->findMappingFile("Foo\\stdClass"));
    }

    public function testFindMappingFileNotFound(): void
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator([$path => $prefix], ".yml");

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("No mapping file found named 'stdClass2.yml' for class 'Foo\stdClass2'.");
        $locator->findMappingFile("Foo\\stdClass2");
    }

    public function testFindMappingFileLeastSpecificNamespaceFirst(): void
    {
        // Low -> High
        $prefixes = array();
        $prefixes[__DIR__ . "/_match_ns"] = "Foo";
        $prefixes[__DIR__ . "/_match_ns/Bar"] = "Foo\\Bar";

        $locator = new SymfonyFileLocator($prefixes, ".yml");

        $this->assertEquals(
            __DIR__ . "/_match_ns/Bar/barEntity.yml",
            $locator->findMappingFile("Foo\\Bar\\barEntity")
        );
    }

    public function testFindMappingFileMostSpecificNamespaceFirst(): void
    {
        $prefixes = array();
        $prefixes[__DIR__ . "/_match_ns/Bar"] = "Foo\\Bar";
        $prefixes[__DIR__ . "/_match_ns"] = "Foo";

        $locator = new SymfonyFileLocator($prefixes, ".yml");

        $this->assertEquals(
            __DIR__ . "/_match_ns/Bar/barEntity.yml",
            $locator->findMappingFile("Foo\\Bar\\barEntity")
        );
    }
}
