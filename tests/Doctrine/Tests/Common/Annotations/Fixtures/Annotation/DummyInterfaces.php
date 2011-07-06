<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Proxy\Proxyable;

/**
 * A description of this class.
 *
 * Let's see if the parser recognizes that this @ is not really referring to an
 * annotation. Also make sure that @var \ is not concated to "@var\is".
 *
 * @author robo
 * @since 2.0
 * @IDummyAnnotation(dummyValue="hello")
 */
class DummyClassWithInterface
{

    /**
     * A nice property.
     *
     * @var mixed
     * @IDummyAnnotation(dummyValue="fieldHello")
     */
    private $field1;
    /**
     * @IDummyJoinTable(name="join_table",
     *      joinColumns={@IDummyJoinColumn(name="col1", referencedColumnName="col2")},
     *      inverseJoinColumns={
     *          @IDummyJoinColumn(name="col3", referencedColumnName="col4")
     *      })
     */
    private $field2;

    /**
     * Gets the value of field1.
     *
     * @return mixed
     * @IDummyAnnotation({1,2,"three"})
     */
    public function getField1()
    {
        
    }

}

interface IDummyId extends Annotation,Proxyable
{
    
}

interface IDummyColumn extends  Annotation,Proxyable
{

    function type();

}

interface IDummyGeneratedValue extends  Annotation,Proxyable
{
    
}

interface IDummyAnnotation extends  Annotation,Proxyable
{

    function dummyValue();

}

interface IDummyJoinColumn extends  Annotation,Proxyable
{

    function name();
    function referencedColumnName();

}

interface IDummyJoinTable extends  Annotation,Proxyable
{

    function name();
    function joinColumns();
    function inverseJoinColumns();
}