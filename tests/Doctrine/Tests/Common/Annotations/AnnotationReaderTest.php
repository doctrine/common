<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\IgnoreAnnotation;
use Doctrine\Common\Annotations\IgnorePhpDocumentorAnnotations;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Import;
use ReflectionClass, Doctrine\Common\Annotations\AnnotationReader;

require_once __DIR__ . '/../../TestInit.php';
require_once __DIR__ . '/TopLevelAnnotation.php';

class AnnotationReaderTest extends \Doctrine\Tests\DoctrineTestCase
{
    public static function setUpBeforeClass()
    {
        // causes the annotation to be auto-loaded
        new Import(array('value' => 'namespace'));
        new IgnorePhpDocumentorAnnotations();
        new IgnoreAnnotation(array('value' => 'foo'));
    }

    public function testAnnotations()
    {
        $reader = new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache);
        $reader->setDefaultAnnotationNamespace('Doctrine\Tests\Common\Annotations\\');

        $this->assertFalse($reader->getAutoloadAnnotations());
        $reader->setAutoloadAnnotations(true);
        $this->assertTrue($reader->getAutoloadAnnotations());
        $reader->setAutoloadAnnotations(false);
        $this->assertFalse($reader->getAutoloadAnnotations());

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClass');
        $classAnnots = $reader->getClassAnnotations($class);

        $annotName = 'Doctrine\Tests\Common\Annotations\DummyAnnotation';
        $this->assertEquals(1, count($classAnnots));
        $this->assertTrue($classAnnots[$annotName] instanceof DummyAnnotation);
        $this->assertEquals("hello", $classAnnots[$annotName]->dummyValue);

        $field1Prop = $class->getProperty('field1');
        $propAnnots = $reader->getPropertyAnnotations($field1Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertTrue($propAnnots[$annotName] instanceof DummyAnnotation);
        $this->assertEquals("fieldHello", $propAnnots[$annotName]->dummyValue);

        $getField1Method = $class->getMethod('getField1');
        $methodAnnots = $reader->getMethodAnnotations($getField1Method);
        $this->assertEquals(1, count($methodAnnots));
        $this->assertTrue($methodAnnots[$annotName] instanceof DummyAnnotation);
        $this->assertEquals(array(1, 2, "three"), $methodAnnots[$annotName]->value);

        $field2Prop = $class->getProperty('field2');
        $propAnnots = $reader->getPropertyAnnotations($field2Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertTrue(isset($propAnnots['Doctrine\Tests\Common\Annotations\DummyJoinTable']));
        $joinTableAnnot = $propAnnots['Doctrine\Tests\Common\Annotations\DummyJoinTable'];
        $this->assertEquals(1, count($joinTableAnnot->joinColumns));
        $this->assertEquals(1, count($joinTableAnnot->inverseJoinColumns));
        $this->assertTrue($joinTableAnnot->joinColumns[0] instanceof DummyJoinColumn);
        $this->assertTrue($joinTableAnnot->inverseJoinColumns[0] instanceof DummyJoinColumn);
        $this->assertEquals('col1', $joinTableAnnot->joinColumns[0]->name);
        $this->assertEquals('col2', $joinTableAnnot->joinColumns[0]->referencedColumnName);
        $this->assertEquals('col3', $joinTableAnnot->inverseJoinColumns[0]->name);
        $this->assertEquals('col4', $joinTableAnnot->inverseJoinColumns[0]->referencedColumnName);

        $dummyAnnot = $reader->getMethodAnnotation($class->getMethod('getField1'), 'Doctrine\Tests\Common\Annotations\DummyAnnotation');
        $this->assertEquals('', $dummyAnnot->dummyValue);
        $this->assertEquals(array(1, 2, 'three'), $dummyAnnot->value);

        $dummyAnnot = $reader->getPropertyAnnotation($class->getProperty('field1'), 'Doctrine\Tests\Common\Annotations\DummyAnnotation');
        $this->assertEquals('fieldHello', $dummyAnnot->dummyValue);

        $classAnnot = $reader->getClassAnnotation($class, 'Doctrine\Tests\Common\Annotations\DummyAnnotation');
        $this->assertEquals('hello', $classAnnot->dummyValue);
    }

    public function testClassSyntaxErrorContext()
    {
        $this->setExpectedException(
            "Doctrine\Common\Annotations\AnnotationException",
            "[Syntax Error] Expected Doctrine\Common\Annotations\Lexer::T_IDENTIFIER, got ')' at position 18 in class ".
            "Doctrine\Tests\Common\Annotations\DummyClassSyntaxError."
        );

        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClassSyntaxError');

        $reader = $this->createAnnotationReader();
        $reader->getClassAnnotations($class);
    }

    public function testMethodSyntaxErrorContext()
    {
        $this->setExpectedException(
            "Doctrine\Common\Annotations\AnnotationException",
            "[Syntax Error] Expected Doctrine\Common\Annotations\Lexer::T_IDENTIFIER, got ')' at position 18 in ".
            "method Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError::foo()."
        );

        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError');
        $method = $class->getMethod('foo');

        $reader = $this->createAnnotationReader();
        $reader->getMethodAnnotations($method);
    }

    public function testPropertySyntaxErrorContext()
    {
        $this->setExpectedException(
            "Doctrine\Common\Annotations\AnnotationException",
            "[Syntax Error] Expected Doctrine\Common\Annotations\Lexer::T_IDENTIFIER, got ')' at position 18 in ".
            "property Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError::\$foo."
        );

        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError');
        $property = $class->getProperty('foo');

        $reader = $this->createAnnotationReader();
        $reader->getPropertyAnnotations($property);
    }

    /**
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader = $this->createAnnotationReader();
        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClass2');
        $annotations = $reader->getPropertyAnnotations($class->getProperty('id'));
        $this->assertEquals(3, count($annotations));
    }

    public function testCustomAnnotationCreationFunction()
    {
        $reader = $this->createAnnotationReader();
        $reader->setAnnotationCreationFunction(function($name, $values) {
            if ($name == 'Doctrine\Tests\Common\Annotations\DummyAnnotation') {
                $a = new CustomDummyAnnotationClass;
                $a->setDummyValue($values['dummyValue']);
                return $a;
            }
        });

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClass');
        $classAnnots = $reader->getClassAnnotations($class);
        $this->assertTrue(isset($classAnnots['Doctrine\Tests\Common\Annotations\CustomDummyAnnotationClass']));
        $annot = $classAnnots['Doctrine\Tests\Common\Annotations\CustomDummyAnnotationClass'];
        $this->assertEquals('hello', $annot->getDummyValue());
    }

    public function testNonAnnotationProblem()
    {
        $reader = new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache);
        $reader->setDefaultAnnotationNamespace('Doctrine\Tests\Common\Annotations\\');

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClassNonAnnotationProblem');
        $annotations = $reader->getPropertyAnnotations($class->getProperty('foo'));
        $this->assertArrayHasKey('Doctrine\Tests\Common\Annotations\DummyAnnotation', $annotations);
        $this->assertType('Doctrine\Tests\Common\Annotations\DummyAnnotation', $annotations['Doctrine\Tests\Common\Annotations\DummyAnnotation']);
    }

    /**
     * @return AnnotationReader
     */
    public function createAnnotationReader($addDefaultNamespace = true)
    {
        $reader = new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache);

        if ($addDefaultNamespace) {
            $reader->setDefaultAnnotationNamespace('Doctrine\Tests\Common\Annotations\\');
        }

        return $reader;
    }

    /**
     * @group DCOM-25
     */
    public function testSetAutoloadAnnotations()
    {
        $reader = $this->createAnnotationReader();
        $reader->setAutoloadAnnotations(true);
        $this->assertTrue($reader->getAutoloadAnnotations());
    }

    public function testImportWithEntireSubNamespace()
    {
        $reader = $this->createAnnotationReader(false);
        $reader->setIndexByClass(false);
        $property = new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestImportWithEntireSubNamespace', 'field');
        $annotations = $reader->getPropertyAnnotations($property);
        $this->assertEquals(1, count($annotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\DummyAnnotation', $annotations[0]);
    }

    public function testImportWithConcreteAnnotation()
    {
        $reader = $this->createAnnotationReader(false);
        $reader->setIndexByClass(false);
        $property = new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestImportWithConcreteAnnotation', 'field');
        $annotations = $reader->getPropertyAnnotations($property);
        $this->assertEquals(1, count($annotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\DummyAnnotation', $annotations[0]);
    }

    public function testImportWithInheritance()
    {
        $reader = $this->createAnnotationReader(false);
        $reader->setIndexByClass(false);

        $class = new TestParentClass();
        $ref = new \ReflectionClass($class);

        $childAnnotations = $reader->getPropertyAnnotations($ref->getProperty('child'));
        $this->assertEquals(1, count($childAnnotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Foo\Name', $childAnnotations[0]);

        $parentAnnotations = $reader->getPropertyAnnotations($ref->getProperty('parent'));
        $this->assertEquals(1, count($parentAnnotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Bar\Name', $parentAnnotations[0]);
    }

    public function testImportDetectsConflict()
    {
        $reader = $this->createAnnotationReader(false);

        try {
            $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestConflictClass', 'field'));
            $this->fail('import conflict was not detected.');
        } catch (AnnotationException $ex) {
            $this->assertEquals('[Semantical Error] The annotation "@Name" was found in several imports: Doctrine\Tests\Common\Annotations\Foo\Name, Doctrine\Tests\Common\Annotations\Bar\Name', $ex->getMessage());
        }
    }

    public function testImportDetectsNotImportedAnnotation()
    {
        $reader = $this->createAnnotationReader(false);
        $reader->setIgnoreNotImportedAnnotations(false);

        try {
            $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestAnnotationNotImportedClass', 'field'));
            $this->fail('not imported annotation was not detected.');
        } catch (AnnotationException $ex) {
            $this->assertEquals('[Semantical Error] The annotation "@Name" was never imported.', $ex->getMessage());
        }
    }

    public function testImportDetectsNonExistentAnnotation()
    {
        $reader = $this->createAnnotationReader(false);

        try {
            $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestNonExistentAnnotationClass', 'field'));
            $this->fail('non-existent annotation was not detected.');
        } catch (AnnotationException $ex) {
            $this->assertEquals('[Semantical Error] The annotation "@Foo\Bar\Name" does not exist, or could not be auto-loaded.', $ex->getMessage());
        }
    }

    public function testImportDetectsNonExistentImportedAnnotation()
    {
        $reader = $this->createAnnotationReader(false);

        try {
            $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestNonExistentImportedAnnotationClass', 'field'));
            $this->fail('non-existent, imported annotation was not detected.');
        } catch (AnnotationException $ex) {
            $this->assertEquals('[Semantical Error] The imported annotation class "Foo\Bar\Name" does not exist.', $ex->getMessage());
        }
    }

    public function testTopLevelAnnotation()
    {
        $reader = $this->createAnnotationReader(false);
        $annotations = $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestTopLevelAnnotationClass', 'field'));

        $this->assertEquals(1, count($annotations));
        $this->assertInstanceOf('\TopLevelAnnotation', $annotations['TopLevelAnnotation']);
    }

    public function testIgnoresPhpDocumentorAnnotations()
    {
        $reader = $this->createAnnotationReader(false);
        $reader->setIgnoreNotImportedAnnotations(false);

        $annotations = $reader->getMethodAnnotations(new \ReflectionMethod('Doctrine\Tests\Common\Annotations\TestIgnorePhpDocumentorAnnotationsClass', 'test'));
        $this->assertEquals(1, count($annotations));
    }
}

/**
 * @import("Doctrine\Tests\Common\Annotations\*")
 * @ignorePhpDocumentorAnnotations
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class TestIgnorePhpDocumentorAnnotationsClass
{
    /**
     * @DummyAnnotation(dummyValue = "foo")
     * @param string $foo
     * @param string $bar
     * @return array
     */
    public function test($foo, $bar)
    {
        return array();
    }
}

class TestTopLevelAnnotationClass
{
    /**
     * @\TopLevelAnnotation
     */
    private $field;
}

/**
 * @import("Foo\Bar\Name")
 */
class TestNonExistentImportedAnnotationClass
{
    /**
     * @Name
     */
    private $field;
}

class TestNonExistentAnnotationClass
{
    /**
     * @Foo\Bar\Name
     */
    private $field;
}

class TestAnnotationNotImportedClass
{
    /**
     * @Name
     */
    private $field;
}

/**
 * @import("Doctrine\Tests\Common\Annotations\Foo\*")
 * @import("Doctrine\Tests\Common\Annotations\Bar\Name")
 */
class TestConflictClass
{
    /**
     * @Name(name = "foo")
     */
    private $field;
}

/**
 * @import("Doctrine\Tests\Common\Annotations\Foo\*")
 */
class TestChildClass
{
    /**
     * @Name(name = "foo")
     */
    protected $child;
}

/**
 * @import("Doctrine\Tests\Common\Annotations\Bar\*")
 */
class TestParentClass extends TestChildClass
{
    /**
     * @Name(name = "bar")
     */
    private $parent;
}

/**
 * @import("Doctrine\Tests\Common\Annotations\DummyAnnotation")
 */
class TestImportWithConcreteAnnotation
{
    /**
     * @DummyAnnotation(dummyValue = "bar")
     */
    private $field;
}

/**
 * @import("Doctrine\Tests\Common\Annotations\*")
 */
class TestImportWithEntireSubNamespace
{
    /**
     * @DummyAnnotation(dummyValue = "foo")
     */
    private $field;
}

class CustomDummyAnnotationClass {
    private $dummyValue;

    public function setDummyValue($value) {
        $this->dummyValue = $value;
    }

    public function getDummyValue() {
        return $this->dummyValue;
    }
}

/**
 * A description of this class.
 *
 * @author robo
 * @since 2.0
 * @DummyAnnotation(dummyValue="hello")
 */
class DummyClass {
    /**
     * A nice property.
     *
     * @var mixed
     * @DummyAnnotation(dummyValue="fieldHello")
     */
    private $field1;

    /**
     * @DummyJoinTable(name="join_table",
     *      joinColumns={
     *          @DummyJoinColumn(name="col1", referencedColumnName="col2")
     *      },
     *      inverseJoinColumns={
     *          @DummyJoinColumn(name="col3", referencedColumnName="col4")
     *      })
     */
    private $field2;

    /**
     * Gets the value of field1.
     *
     * @return mixed
     * @DummyAnnotation({1,2,"three"})
     */
    public function getField1() {
    }
}

class DummyClass2 {
    /**
     * @DummyId @DummyColumn(type="integer") @DummyGeneratedValue
     * @var integer
     */
    private $id;
}

class DummyId extends \Doctrine\Common\Annotations\Annotation {}
class DummyColumn extends \Doctrine\Common\Annotations\Annotation {
    public $type;
}
class DummyGeneratedValue extends \Doctrine\Common\Annotations\Annotation {}
class DummyAnnotation extends \Doctrine\Common\Annotations\Annotation {
    public $dummyValue;
}
class DummyJoinColumn extends \Doctrine\Common\Annotations\Annotation {
    public $name;
    public $referencedColumnName;
}
class DummyJoinTable extends \Doctrine\Common\Annotations\Annotation {
    public $name;
    public $joinColumns;
    public $inverseJoinColumns;
}

/**
 * @DummyAnnotation(@)
 */
class DummyClassSyntaxError
{

}

class DummyClassMethodSyntaxError
{
    /**
     * @DummyAnnotation(@)
     */
    public function foo()
    {

    }
}

class DummyClassPropertySyntaxError
{
    /**
     * @DummyAnnotation(@)
     */
    public $foo;
}

class DummyClassNonAnnotationProblem
{
    /**
     * @DummyAnnotation
     *
     * @var \Test
     */
    public $foo;
}

namespace Doctrine\Tests\Common\Annotations\Foo;

class Name extends \Doctrine\Common\Annotations\Annotation
{
    public $name;
}

namespace Doctrine\Tests\Common\Annotations\Bar;

class Name extends \Doctrine\Common\Annotations\Annotation
{
    public $name;
}

