<?php

namespace Doctrine\Tests\Common\Reflection;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Reflection\StaticReflectionParser;
use Doctrine\Common\Reflection\Psr0FindFile;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class StaticReflectionParserTest extends DoctrineTestCase
{
    public function testParentClass()
    {
        $testsRoot = substr(__DIR__, 0, -strlen(__NAMESPACE__) - 1);
        $paths = array(
            'Doctrine\\Tests' => array($testsRoot),
        );
        $noParentClassName = 'Doctrine\\Tests\\Common\\Reflection\\NoParent';
        $staticReflectionParser = new StaticReflectionParser($noParentClassName, new Psr0FindFile($paths));
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($noParentClassName, $declaringClassName);

        $className = 'Doctrine\\Tests\\Common\\Reflection\\FullyClassifiedParent';
        $staticReflectionParser = new StaticReflectionParser($className, new Psr0FindFile($paths));
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($noParentClassName, $declaringClassName);

        $className = 'Doctrine\\Tests\\Common\\Reflection\\SameNamespaceParent';
        $staticReflectionParser = new StaticReflectionParser($className, new Psr0FindFile($paths));
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($noParentClassName, $declaringClassName);

        $dummyParentClassName = 'Doctrine\\Tests\\Common\\Reflection\\Dummies\\NoParent';

        $className = 'Doctrine\\Tests\\Common\\Reflection\\DeeperNamespaceParent';
        $staticReflectionParser = new StaticReflectionParser($className, new Psr0FindFile($paths));
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($dummyParentClassName, $declaringClassName);

        $className = 'Doctrine\\Tests\\Common\\Reflection\\UseParent';
        $staticReflectionParser = new StaticReflectionParser($className, new Psr0FindFile($paths));
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($dummyParentClassName, $declaringClassName);

    }

    public function testClassAnnotationOnly()
    {
        $className = 'Doctrine\Tests\Common\Annotations\DummyClass';
        $testsRoot = substr(__DIR__, 0, -strlen(__NAMESPACE__) - 1);
        $paths = array(
            'Doctrine\\Tests' => array($testsRoot),
        );
        AnnotationRegistry::registerAutoloadNamespace('Doctrine\\Tests', array($testsRoot));
        $staticReflectionParser = new StaticReflectionParser($className, new Psr0FindFile($paths), TRUE);
        $class = $staticReflectionParser->getReflectionClass();
        $reader = new AnnotationReader();
        $this->assertEquals(1, count($reader->getClassAnnotations($class)));
        $this->assertInstanceOf($annotName = 'Doctrine\Tests\Common\Annotations\DummyAnnotation', $annot = $reader->getClassAnnotation($class, $annotName));
        $this->assertEquals("hello", $annot->dummyValue);

    }
}
