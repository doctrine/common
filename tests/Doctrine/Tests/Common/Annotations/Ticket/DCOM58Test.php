<?php
namespace Doctrine\Tests\Common\Annotations\Ticket;

//Some class named Entity in the global namespace
include __DIR__ .'/DCOM58Entity.php';

/**
 * @group DCOM58
 */
class DCOM58Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class "xxx" is not a valid entity or mapped super class.
     */
    public function testIssue()
    {
        $reader     = new \Doctrine\Common\Annotations\AnnotationReader();
        $result     = $reader->getClassAnnotations(new \ReflectionClass(__NAMESPACE__."\MappedClass"));

        foreach ($result as $annot) {
            $classAnnotations[get_class($annot)] = $annot;
        }
        // Evaluate Entity annotation
        if (!isset($classAnnotations['Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM\Mapping\Entity'])) {
            throw new \RuntimeException('Class "xxx" is not a valid entity or mapped super class.');
        }
    }

    public function testIssueGlobalNamespace()
    {
        $docblock   = "@Entity";
        $parser     = new \Doctrine\Common\Annotations\DocParser();
        $parser->setImports(array(
            "__NAMESPACE__" =>"Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM\Mapping"
        ));

        $annots     = $parser->parse($docblock);

        $this->assertEquals(1, count($annots));
        $this->assertInstanceOf("Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM\Mapping\Entity", $annots[0]);
    }
}

/**
 * @Entity
 */
class MappedClass
{

}


namespace Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM\Mapping;
/**
* @Annotation
*/
class Entity
{

}

namespace Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM;
/**
* @Annotation
*/
class Entity
{

}
