<?php

namespace Doctrine\Tests\Common\Reflection;

use Doctrine\Tests\Common\Reflection\NoParent;
use Doctrine\Tests\Common\Reflection\Dummies\NoParent as NoParentDummy;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Reflection\StaticReflectionParser;
use Doctrine\Common\Reflection\Psr0FindFile;

class StaticReflectionParserTest extends DoctrineTestCase
{
    /**
     * @dataProvider parentClassData
     *
     * @param bool $classAnnotationOptimize
     * @param string $parsedClassName
     * @param string $expectedClassName
     *
     * @return void
     */
    public function testParentClass($classAnnotationOptimize, $parsedClassName, $expectedClassName)
    {
        // If classed annotation optimization is enabled the properties tested
        // below cannot be found.
        if ($classAnnotationOptimize) {
            $this->setExpectedException('ReflectionException');
        }

        $testsRoot = substr(__DIR__, 0, -strlen(__NAMESPACE__) - 1);
        $paths = array(
            'Doctrine\\Tests' => array($testsRoot),
        );
        $staticReflectionParser = new StaticReflectionParser($parsedClassName, new Psr0FindFile($paths), $classAnnotationOptimize);
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($expectedClassName, $declaringClassName);

    }

    /**
     * @return array
     */
    public function parentClassData()
    {
        $data = array();
        $noParentClassName = NoParent::class;
        $dummyParentClassName = NoParentDummy::class;
        foreach (array(false, true) as $classAnnotationOptimize) {
            $data[] = array(
              $classAnnotationOptimize, $noParentClassName, $noParentClassName,
            );
            $data[] = array(
              $classAnnotationOptimize, FullyClassifiedParent::class, $noParentClassName,
            );
            $data[] = array(
              $classAnnotationOptimize, SameNamespaceParent::class, $noParentClassName,
            );
            $data[] = array(
              $classAnnotationOptimize, DeeperNamespaceParent::class, $dummyParentClassName,
            );
            $data[] = array(
              $classAnnotationOptimize, UseParent::class, $dummyParentClassName,
            );
        }
        return $data;
    }

    /**
     * @dataProvider classAnnotationOptimize
     */
    public function testClassAnnotationOptimizedParsing($classAnnotationOptimize) {
        $testsRoot = substr(__DIR__, 0, -strlen(__NAMESPACE__) - 1);
        $paths = array(
          'Doctrine\\Tests' => array($testsRoot),
        );
        $staticReflectionParser = new StaticReflectionParser(ExampleAnnotationClass::class, new Psr0FindFile($paths), $classAnnotationOptimize);
        $expectedDocComment = '/**
 * @Annotation(
 *   key = "value"
 * )
 */';
        $this->assertEquals($expectedDocComment, $staticReflectionParser->getDocComment('class'));
    }

    /**
     * @return array
     */
    public function classAnnotationOptimize()
    {
        return array(
            array(false),
            array(true)
        );
    }
}
