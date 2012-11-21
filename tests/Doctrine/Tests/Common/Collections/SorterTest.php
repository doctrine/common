<?php
namespace Doctrine\Tests\Common\Collections;

use Doctrine\Common\Collections\ArrayCollection;

class SorterTest extends \Doctrine\Tests\DoctrineTestCase
{
    
    public function testNumericalArray()
    {
        $array = array();
        for($i = 0; $i < 100; $i++)
        {
            $object = new \stdClass();
            $object->value = rand(1, 500);
            $array[] = $object;
        }
        
        $collection = new ArrayCollection($array);
        
        $collection->sort(array('value'));
        
        for($i = 1; $i < 100; $i++)
        {
            $previous = $collection->get($i - 1);
            $current = $collection->get($i);
            $this->assertGreaterThanOrEqual($previous->value, $current->value);
        }
    }
    
    public function testMultiNumericalArray()
    {
        $array = array();        
        for($i = 0; $i < 1000; $i++)
        {
            $object = new \stdClass();
            $object->firstValue = rand(1, 100);
            $object->secondValue = rand(1, 100);
            $object->thirdValue = rand(1, 100);
            $array[] = $object;
        }
        
        $collection = new ArrayCollection($array);

        $collection->sort(array('firstValue', 'secondValue', 'thirdValue'));
        
        for($i = 1; $i < 1000; $i++)
        {
            
            $previous = $collection->get($i - 1);
            $current = $collection->get($i);
            $this->assertGreaterThanOrEqual($previous->firstValue, $current->firstValue);
            if($previous->firstValue == $current->firstValue)
            {                
                $this->assertGreaterThanOrEqual($previous->secondValue, $current->secondValue);
                if($previous->thirdValue == $current->thirdValue)
                {
                    $this->assertGreaterThanOrEqual($previous->thirdValue, $current->thirdValue);
                }
            }
        }
    }
    
    public function testMultiNumericalArrayAscend()
    {
        $array = array();
        for($i = 0; $i < 10; $i++)
        {
            $object = new \stdClass();
            $object->firstValue = rand(1, 5);
            $object->secondValue = rand(1, 5);
            $object->thirdValue = rand(1, 5);
            $array[] = $object;
        }
        
        $collection = new ArrayCollection($array);
        
        $collection->sort(array('firstValue', 'secondValue', 'thirdValue'), true);

        for($i = 1; $i < 10; $i++)
        {
            
            $previous = $collection->get($i - 1);
            $current = $collection->get($i);
            $this->assertLessThanOrEqual($previous->firstValue, $current->firstValue);
            if($previous->firstValue == $current->firstValue)
            {                
                $this->assertLessThanOrEqual($previous->secondValue, $current->secondValue);
                if($previous->thirdValue == $current->thirdValue)
                {
                    $this->assertLessThanOrEqual($previous->thirdValue, $current->thirdValue);
                }
            }
        }
    }
    
}
