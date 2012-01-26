<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationReaderTest extends AbstractReaderTest
{
    protected function getReader()
    {
        return new AnnotationReader();
    }


    /**
     * @group PhpAnnotations
     */
    public function testPhpAnnotations()
    {
        $reader = $this->getReader();
        $reader->setIgnorePhpAnnotations(false);

        $class  = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassWithPhpAnnotations');
        
        $this->assertEquals(1,count($fooAnnot       = $reader->getPropertyAnnotations($class->getProperty('foo'))));
        $this->assertEquals(2,count($barAnnot       = $reader->getPropertyAnnotations($class->getProperty('bar'))));
        $this->assertEquals(2,count($foobarAnnot    = $reader->getPropertyAnnotations($class->getProperty('foobar'))));
        $this->assertEquals(3,count($functionAnnot  = $reader->getMethodAnnotations($class->getMethod('someFunction'))));

        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetPropertyMethod', $barAnnot[1]);
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll', $foobarAnnot[1]);
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll', $functionAnnot[2]);

        $this->assertInstanceOf('Doctrine\Common\Annotations\Annotation\VarAnnotation', $fooAnnot[0]);
        $this->assertInstanceOf('Doctrine\Common\Annotations\Annotation\VarAnnotation', $barAnnot[0]);
        $this->assertInstanceOf('Doctrine\Common\Annotations\Annotation\VarAnnotation', $foobarAnnot[0]);

        $this->assertInstanceOf('Doctrine\Common\Annotations\Annotation\ParamAnnotation', $functionAnnot[0]);
        $this->assertInstanceOf('Doctrine\Common\Annotations\Annotation\ReturnAnnotation', $functionAnnot[1]);

        $this->assertEquals('integer', $fooAnnot[0]->value);
        $this->assertEquals('bool|string', $barAnnot[0]->value);
        $this->assertEquals('array<float>', $foobarAnnot[0]->value);
        $this->assertEquals('float', $functionAnnot[0]->value);
        $this->assertEquals('boolean', $functionAnnot[1]->value);
    }
}