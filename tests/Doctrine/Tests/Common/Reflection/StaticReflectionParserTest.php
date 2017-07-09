<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Reflection;

use Doctrine\Tests\Common\Reflection\NoParent;
use Doctrine\Tests\Common\Reflection\Dummies\NoParent as NoParentDummy;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Reflection\StaticReflectionParser;
use Doctrine\Common\Reflection\Psr0FindFile;
use ReflectionException;

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
    public function testParentClass(bool $classAnnotationOptimize, string $parsedClassName, string $expectedClassName): void
    {
        // If classed annotation optimization is enabled the properties tested
        // below cannot be found.
        if ($classAnnotationOptimize) {
            $this->expectException(ReflectionException::class);
        }

        $testsRoot = substr(__DIR__, 0, -strlen(__NAMESPACE__) - 1);
        $paths = [
            'Doctrine\\Tests' => [$testsRoot],
        ];
        $staticReflectionParser = new StaticReflectionParser($parsedClassName, new Psr0FindFile($paths), $classAnnotationOptimize);
        $declaringClassName = $staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', 'test')->getClassName();
        $this->assertEquals($expectedClassName, $declaringClassName);

    }

    /**
     * @return array
     */
    public function parentClassData(): array
    {
        $data = [];
        $noParentClassName = NoParent::class;
        $dummyParentClassName = NoParentDummy::class;
        foreach ([false, true] as $classAnnotationOptimize) {
            $data[] = [
              $classAnnotationOptimize, $noParentClassName, $noParentClassName,
            ];
            $data[] = [
              $classAnnotationOptimize, FullyClassifiedParent::class, $noParentClassName,
            ];
            $data[] = [
              $classAnnotationOptimize, SameNamespaceParent::class, $noParentClassName,
            ];
            $data[] = [
              $classAnnotationOptimize, DeeperNamespaceParent::class, $dummyParentClassName,
            ];
            $data[] = [
              $classAnnotationOptimize, UseParent::class, $dummyParentClassName,
            ];
        }
        return $data;
    }

    /**
     * @dataProvider classAnnotationOptimize
     */
    public function testClassAnnotationOptimizedParsing(bool $classAnnotationOptimize): void
    {
        $testsRoot = substr(__DIR__, 0, -strlen(__NAMESPACE__) - 1);
        $paths = [
          'Doctrine\\Tests' => [$testsRoot],
        ];
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
        return [
            [false],
            [true]
        ];
    }
}
