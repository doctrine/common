<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;

class SymfonyFileLocatorTest extends DoctrineTestCase
{
    public function testGetPaths()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix));
        $this->assertEquals(array($path), $locator->getPaths());

        $locator = new SymfonyFileLocator(array($path => $prefix));
        $this->assertEquals(array($path), $locator->getPaths());
    }

    public function testGetPrefixes()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix));
        $this->assertEquals(array($path => $prefix), $locator->getNamespacePrefixes());
    }

    public function testGetFileExtension()
    {
        $locator = new SymfonyFileLocator(array(), ".yml");
        $this->assertEquals(".yml", $locator->getFileExtension());
        $locator->setFileExtension(".xml");
        $this->assertEquals(".xml", $locator->getFileExtension());
    }

    public function testFileExists()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix), ".yml");

        $this->assertTrue($locator->fileExists("Foo\stdClass"));
        $this->assertTrue($locator->fileExists("Foo\global"));
        $this->assertFalse($locator->fileExists("Foo\stdClass2"));
        $this->assertFalse($locator->fileExists("Foo\global2"));
    }

    public function testGetAllClassNames()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix), ".yml");
        $classes = $locator->getAllClassNames(null);
        sort($classes);

        $this->assertEquals(array("Foo\\global", "Foo\\stdClass"), $classes);
        $this->assertEquals(array("Foo\\stdClass"), $locator->getAllClassNames("global"));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Namespace separator should not be empty
     */
    public function testInvalidCustomNamespaceSeparator()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        new SymfonyFileLocator(array($path => $prefix), ".yml", null);
    }

    public function customNamespaceSeparatorProvider()
    {
        return array(
            'directory separator' => array(DIRECTORY_SEPARATOR, "/_custom_ns/dir"),
            'default dot separator' => array('.', "/_custom_ns/dot"),
        );
    }

    /**
     * @dataProvider customNamespaceSeparatorProvider
     *
     * @param $separator string Directory separator to test against
     * @param $dir       string Path to load mapping data from
     *
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    public function testGetClassNamesWithCustomNsSeparator($separator, $dir)
    {
        $path = __DIR__ . $dir;
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix), ".yml", $separator);
        $classes = $locator->getAllClassNames(null);
        sort($classes);

        $this->assertEquals(array("Foo\\stdClass", "Foo\\sub\\subClass", "Foo\\sub\\subsub\\subSubClass"), $classes);
    }

    public function customNamespaceLookupQueryProvider()
    {
        return array(
            'directory separator'  => array(
                DIRECTORY_SEPARATOR,
                "/_custom_ns/dir",
                array(
                    "stdClass.yml"               => "Foo\\stdClass",
                    "sub/subClass.yml"           => "Foo\\sub\\subClass",
                    "sub/subsub/subSubClass.yml" => "Foo\\sub\\subsub\\subSubClass",
                )
            ),
            'default dot separator' => array(
                '.',
                "/_custom_ns/dot",
                array(
                    "stdClass.yml"               => "Foo\\stdClass",
                    "sub.subClass.yml"           => "Foo\\sub\\subClass",
                    "sub.subsub.subSubClass.yml" => "Foo\\sub\\subsub\\subSubClass",
                )
            ),
        );
    }

    /** @dataProvider customNamespaceLookupQueryProvider
     * @param $separator string Directory separator to test against
     * @param $dir       string Path to load mapping data from
     * @param $files     array  Files to lookup classnames
     *
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    public function testFindMappingFileWithCustomNsSeparator($separator, $dir, $files)
    {
        $path   = __DIR__ . $dir;
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix), ".yml", $separator);

        foreach ($files as $filePath => $className) {
            $this->assertEquals(realpath($path .'/'. $filePath), realpath($locator->findMappingFile($className)));
        }

    }


    public function testFindMappingFile()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix), ".yml");

        $this->assertEquals(__DIR__ . "/_files/stdClass.yml", $locator->findMappingFile("Foo\\stdClass"));
    }

    public function testFindMappingFileNotFound()
    {
        $path = __DIR__ . "/_files";
        $prefix = "Foo";

        $locator = new SymfonyFileLocator(array($path => $prefix), ".yml");

        $this->setExpectedException(
            "Doctrine\Common\Persistence\Mapping\MappingException",
            "No mapping file found named '".__DIR__."/_files/stdClass2.yml' for class 'Foo\stdClass2'."
        );
        $locator->findMappingFile("Foo\\stdClass2");
    }
}
