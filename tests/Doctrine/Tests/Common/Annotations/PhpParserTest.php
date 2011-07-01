<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\PhpParser;

require_once __DIR__.'/AnnotationReaderTest.php';
require_once __DIR__.'/Fixtures/NonNamespacedClass.php';

class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseClassWithMultipleClassesInFile()
    {
        $class = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MultipleClassesInFile');
        $parser = new PhpParser();

        $this->assertEquals(array(
            'route'  => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route',
            'secure' => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testParseClassWithMultipleImportsInUseStatement()
    {
        $class = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MultipleImportsInUseStatement');
        $parser = new PhpParser();

        $this->assertEquals(array(
            'route'  => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route',
            'secure' => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testParseClassWhenNotUserDefined()
    {
        $parser = new PhpParser();
        $this->assertEquals(array(), $parser->parseClass(new \ReflectionClass('\stdClass')));
    }

    public function testParseClassWhenClassIsNotNamespaced()
    {
        $parser = new PhpParser();
        $class = new \ReflectionClass('\AnnotationsTestsFixturesNonNamespacedClass');

        $this->assertEquals(array(
            'route'  => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route',
            'template' => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsInterface()
    {
        $parser = new PhpParser();
        $class = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\TestInterface');

        $this->assertEquals(array(
            'secure' => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }
    
    
    public function testParseClassWhenAnnotationIsAnnnoted()
    {
        $parser     = new PhpParser();
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation');
        
        $defaultvalue   = 'Doctrine\Common\Annotations\Marker\Annotation\DefaultValue';
        $target         = 'Doctrine\Common\Annotations\Marker\Annotation\Target';
        $type           = 'Doctrine\Common\Annotations\Marker\Annotation\Type';
                

        $parsed     = $parser->parseClass($class);
        
        
        $this->assertTrue(array_key_exists('defaultvalue', $parsed));
        $this->assertTrue(array_key_exists('target', $parsed));
        $this->assertTrue(array_key_exists('type', $parsed));
        
        $this->assertEquals($parsed['defaultvalue'], $defaultvalue);
        $this->assertEquals($parsed['target'], $target);
        $this->assertEquals($parsed['type'], $type);
    }
}