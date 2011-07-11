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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationMarkers;

/**
 * Factory for annotations classes.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class AnnotationFactory
{
    /**
     * const
     */
    const DEFAULT_KEY = 'value';

    /**
     * @var array 
     */
    private $classes = array();
    /**
     * @var array 
     */
    private static $properties = array();
    /**
     * @var array 
     */
    private static $hasProperty = array();
    /**
     * @var array 
     */
    private static $hasConstructor = array();
    /**
     * @var array 
     */
    private static $isValid = array();
    /**
     * @var array 
     */
    private static $classExists = array();

    /**
     * @param    string  $className
     * @param    array   $data
     * @return   mixed 
     */
    public function newAnnotation($className, array $data = array())
    {
        if (!$this->classExists($className))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class %s does not exist.', $className)
            );
        }

        if (!$this->isValid($className))
        {
            throw new \InvalidArgumentException(
                    sprintf('The constructor of class %s must be public and type of first parameter should be an array.', $className)
            );
        }
            
        if ($this->hasConstructor($className))
        {
            $annotation = new $className($data);
        } else
        {
            $annotation = new $className();
        }

        $this->setData($className, $annotation, $data);

        return $annotation;
    }

    /**
     * @param  string $className
     * @return bool 
     */
    private function classExists($className)
    {
        if (!isset(self::$classExists[$className]))
        {
            self::$classExists[$className] = class_exists($className);
        }
        return self::$classExists[$className];
    }

    /**
     * @param  string $className
     * @return bool 
     */
    private function hasConstructor($className)
    {
        if (!isset(self::$hasConstructor[$className]))
        {
            self::$hasConstructor[$className] = false;
            $constructor = $this->getClass($className)->getConstructor();
            if ($constructor instanceof \ReflectionMethod)
            {
                if ($constructor->isPublic())
                {
                    self::$hasConstructor[$className] = true;
                }
            }
        }
        return self::$hasConstructor[$className];
    }

    /**
     * @param  string $className
     * @return bool 
     */
    private function isValid($className)
    {
        if (!isset(self::$isValid[$className]))
        {
            self::$isValid[$className] = false;
            $constructor = $this->getClass($className)->getConstructor();
            if ($constructor instanceof \ReflectionMethod)
            {
                if($constructor->isPublic())
                {
                    $required = $constructor->getNumberOfRequiredParameters();
                    if($required == 0)
                    {
                        self::$isValid[$className] = true;
                    }
                    if($required == 1)
                    {
                        $parameters = $constructor->getParameters();
                        $parameter  = reset($parameters);
                        self::$isValid[$className] = $parameter->isArray();
                    }
                }
            }
            else{
                self::$isValid[$className] = true;
            }
        }
        return self::$isValid[$className];
    }

    /**
     * @param   string $className
     * @param   string $property
     * @return  bool
     */
    private function hasProperty($className, $property)
    {
        if (!isset(self::$hasProperty[$className][$property]))
        {
            self::$hasProperty[$className][$property] = $this->getClass($className)->hasProperty($property);
        }
        return self::$hasProperty[$className][$property];
    }

    /**
     * @param   string $className
     * @return \ReflectionClass
     */
    private function getClass($className)
    {
        if (!isset($this->classes[$className]))
        {
            $this->classes[$className] = new \ReflectionClass($className);
        }
        return $this->classes[$className];
    }

    /**
     * @param    string $className
     * @return   bool 
     */
    private function getProperties($className)
    {
        if (!isset(self::$properties[$className]))
        {
            $list = (array) $this->getClass($className)->getProperties();
            self::$properties[$className] = array();
            foreach ($list as $property)
            {
                self::$properties[$className][] = $property->getName();
            }
        }
        return self::$properties[$className];
    }

    /**
     * @param string $className
     * @param mixed $object
     * @param array $data 
     */
    private function setData($className, $object, array $data)
    {
        if (!empty($data))
        {

            foreach ($data as $property => $value)
            {
                if (!$this->hasProperty($className, $property))
                {
                    if ($property == self::DEFAULT_KEY)
                    {
                        $properties = $this->getProperties($className);
                        $property = reset($properties);

                        $this->setPropertyValue($className, $object, $property, $value);
                    } else
                    {
                        throw new \BadMethodCallException(
                                sprintf("Unknown property '%s' on object '%s'.", $property, $className)
                        );
                    }
                } else
                {
                    $this->setPropertyValue($className, $object, $property, $value);
                }
            }
        }
    }

    /**
     * @param   string    $className
     * @param   mixed     $instance
     * @param   string    $property
     * @param   mixed     $value
     */
    private function setPropertyValue($className, $instance, $property, $value)
    {
        $prop = $this->getClass($className)->getProperty($property);

        if ($prop->isPublic())
        {
            $instance->{$property} = $value;
        } else
        {
            $prop->setAccessible(true);
            $prop->setValue($instance, $value);
            $prop->setAccessible(false);
        }
    }

}