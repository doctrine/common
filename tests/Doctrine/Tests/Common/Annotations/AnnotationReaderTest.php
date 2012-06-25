<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationReaderTest extends AbstractReaderTest
{
    protected function getReader()
    {
        return new AnnotationReader();
    }
    
    public function testSupportWildcardIgnoredAnnotation()
    {
        $reader = $this->getReader();
        AnnotationReader::addGlobalIgnoredName('Foo\\*');
        
        $class  = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\IgnoreAnnotationWithWildcard');
        $annots = $reader->getClassAnnotations($class);
        
        $this->assertEquals('foo', $annots[0]->bar);
        $this->assertEquals('bar', $annots[1]->foo);
    }
}