<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Collections;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\CommonException;

/**
 * Sorter is used to reorder the elements of an Array Collection after they have
 * been loaded from the database
 *
 * @author  Andrew Tarry <andrew@andrewtarry.com>
 */
class Sorter
{
    
    /**
     * The collection to be sorted
     * @var array
     */
    private $collection;
    
    /**
     * The name of the property or function to sort by
     * @var string
     */
    private $sortBy;
    
    /**
     * Array of elements to be sorted
     * @var array
     */
    private $group = array();
    
    /**
     * Values to be resorted
     * @var array
     */
    private $resort = array();
    
    public function __construct(Collection $collection) {
        $this->collection = $collection->toArray();
    }
    
    public function sortBy(array $sortByValues)
    {
        //Multidemsional array of values to sort by
        $sortArray = array();
             
        //Loop the values being sorted by
        foreach($sortByValues as $sortByKey => $sortBy)
        {           
            
            //Set the class variable sortBy to be this sort by
            $this->sortBy = $sortBy;
                      
            //For the first sorter by value simple sort the whole array and continue
            if($sortByKey == 0)
            {
                usort($this->collection, array($this, "compare"));
                
                //Set the first value of the array to be the collection to begin with
                $sortArray[] = $this->collection;
                continue;
            }    

            
            //Create a new array of elements that will be sorted next time
            $this->resort = array();
            
            $previousSortBy = $sortByValues[$sortByKey - 1];

            /*
             * Loop the values that were sorted in the previous iteration to mark
             * the ones that need to be sorted again
             */
            foreach($sortArray as $sortArrayElement)
            {    

                //Boolean for if this is the first element in an array
                $first = true;
                
                foreach($sortArrayElement as $key => $current)
                {
                    
                                
                    //Skip the first element
                    if($first == true)
                    {
                        $first = false;   
                        continue;
                    }                  

                    //Get the previous item
                    $previous = $sortArrayElement[$key - 1];
                                                            
                    if($this->addElement($current, $previous, $key, $previousSortBy) == false)
                    {   
                        $this->addResort();
                    }
                    
                }
                
                $this->addResort();
            }//End of loop of previous values
            
            //Clear the sort array once its not needed
            $sortArray = array();
            
            /*
             * Loop all of the resort arrays to change their positions
             * $re has the original primary key of the element 
             */            
            foreach($this->resort as $re)
            {
                
                /*
                 * Duplicate the resort element array     
                 * 
                 * The sorted array will be passed to usort so it will lose its
                 * primary key           
                 */
                $sorted = $re;
                
                //Perform the resort on the sorted array
                usort($sorted, array($this, "compare"));
                $pos = 0;
                
                /*
                 * Array of the elements that have been sorted with their new 
                 * primary key
                 */               
                $saved = array();
               
                //Loop the unsorted array and insert the sorted elements
                foreach($re as $rKey => $r)
                {
                    $this->collection[$rKey] = $sorted[$pos];
                    $saved[$rKey] = $sorted[$pos];
                    $pos++;
                }
                
                //Replace the unsorted array with the sorted one
                $sortArray[] = $saved;
                
            }//End of resport loop

            //Clear the resort array
            $this->resort = array();
            
        }//End of the loop of values being sorted by
        return $this->collection;
    }
    
    /**
     * Test the element with the previous one to see if the values match. 
     * 
     * If the values match then the elements should be as a potential pair to be 
     * resorted
     * 
     * @param mixed $current The current element being tested
     * @param mixed $previous The previous element that was tested
     * @param int $key The array key of the element in the collection
     * @param string $method Name of the method that is being compared
     * @return boolean True if the elements match and false if they do not
     */
    private function addElement($current, $previous, $key, $method)
    {
        if($this->getValue($current, $method) == $this->getValue($previous, $method))
        {

            //Add the previous element to the group if it is not there
            if(!in_array($previous, $this->group))
            {
                $this->group[$key - 1] = $previous;
            }

            //Add the current element to the group
            $this->group[$key] = $current;   
            
            return true;
        }else {
            return false;
        }
    }
    
    /**
     * Add the element group to the resort array if it reches the end of the matches
     */
    private function addResort()
    {
        if(count($this->group) > 0){
            $this->resort[] = $this->group;
            $this->group = array();
        }
    }

    /**
     * The compare function being used by usort
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    private function compare($a, $b)
    {
        $a_val = $this->getValue($a, $this->sortBy);
        $b_val = $this->getValue($b, $this->sortBy);

        if($a_val == $b_val)
        {
            return 0;
        }
        return ($a_val > $b_val) ? 1 : -1;
    }
    
    /**
     * Get the method or property of the class that is being sorted by
     * 
     * @param mixed $class class being sorted
     * @param string $sortBy name of the method or property being sorted by
     * @return mixed value being sorted by
     * @throws CommonException Thrown if the method or property does not exist
     */
    private function getValue($class, $sortBy)
    {
        $end = substr($sortBy, -2);
        
        if($end != '()')
        {
            if(property_exists($class, $sortBy))
            {
                return $class->$sortBy;
            }else{
                throw new CommonException('Property ' . $sortBy . ' does not exist for ' . get_class($class));
            }

        }else{
            $methodName = substr($sortBy, 0, -2);
            if(method_exists($class, $methodName))
            {                    
                return $class->$methodName();
            }else{
                throw new CommonException('Method ' . $sortBy . ' does not exist for ' . get_class($class));
            }                
        }        
    }
    
}