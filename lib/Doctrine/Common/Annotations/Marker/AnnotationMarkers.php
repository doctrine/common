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

namespace Doctrine\Common\Annotations\Marker;

use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\Annotation\Marker;
use Doctrine\Common\Annotations\Marker\Strategy\MarkerStrategy;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;
use \ReflectionProperty;

/**
 * AnnotationMarkers
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class AnnotationMarkers
{

    /**
     * @var ReflectionClass
     */
    private $class;
    /**
     * @var Reader 
     */
    private $reader;
    /**
     * @var array
     */
    private $markers = array();
    /**
     * @var array
     */
    private $classMarkers = array();
    /**
     * @var array
     */
    private $methodMarkers = array();
    /**
     * @var array
     */
    private $propertyMarkers = array();
    /**
     * @var array
     */
    private $hasClassMarker = array();
    /**
     * @var array
     */
    private $hasMethodMarker = array();
    /**
     * @var array
     */
    private $hasPropertyMarker = array();

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param \ReflectionClass $class
     * @param Reader $reader 
     */
    public function __construct(\ReflectionClass $class, Reader $reader)
    {
        $this->class = $class;
        $this->reader= $reader;

        $this->readMarkers();
    }

    /**
     * Read annotation markers
     */
    private function readMarkers()
    {
        $this->markers = array();
        $this->readClassMarkers();
        $this->readMethodMarkers();
        $this->readPropertyMarkers();
    }

    /**
     * Read annotation class markers
     */
    private function readClassMarkers()
    {
        $this->classMarkers     = array();
        $this->hasClassMarker   = array();
        $annotations            = $this->getReader()->getClassAnnotations($this->class);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Marker)
            {
                $this->addClassMarker($annotation);
            }
        }
    }

    /**
     * Read annotation method markers
     */
    private function readMethodMarkers()
    {
        $this->methodMarkers = array();
        $this->hasMethodMarker = array();
        $methods = $this->class->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->getReader()->getMethodAnnotations($method);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Marker)
                {
                    $this->addMethodMarker($annotation, $method->getName());
                }
            }
        }
    }

    /**
     * Read annotation property markers
     */
    private function readPropertyMarkers()
    {
        $this->propertyMarkers = array();
        $this->hasPropertyMarker = array();
        $properties = $this->class->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->getReader()->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Marker)
                {
                    $this->addPropertyMarker($annotation, $property->getName());
                }
            }
        }
    }

    /**
     * @param Marker $marker
     */
    private function addClassMarker(Marker $marker)
    {
        $this->markers[] = $marker;
        $this->classMarkers[] = $marker;
    }

    /**
     * @param Marker $marker
     */
    private function addMethodMarker(Marker $marker, $method)
    {
        $this->markers[] = $marker;
        $this->methodMarkers[$method][] = $marker;
    }

    /**
     * @param Marker $marker
     */
    private function addPropertyMarker(Marker $marker, $property)
    {
        $this->markers[] = $marker;
        $this->propertyMarkers[$property][] = $marker;
    }

    /**
     * @param   string  $className
     * @param   array   $markers
     * @return  bool
     */
    private function hasMarker($className, array $markers)
    {
        foreach ($markers as $key => $value) {
            if ($value instanceof $className)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getMarkers()
    {
        return (array) $this->markers;
    }

    /**
     * @return \ReflectionClass 
     */
    public function getAnnotationClass()
    {
        return $this->class;
    }

    /**
     * @param   Marker $marker
     * @return  MarkerStrategy
     */
    public function runMarker($annotation, $target, Marker $marker)
    {
        $strategy = MarkerStrategy::factory($this, $marker);
        if ($strategy instanceof MarkerStrategy)
        {
            $strategy->run($annotation, $target);
            return $strategy;
        }
        return null;
    }

    /**
     * @return array
     */
    public function getClassMarkers()
    {
        return (array) $this->classMarkers;
    }

    /**
     * @param   ReflectionClass $class
     * @return  bool
     */
    public function hasClassMarker(ReflectionClass $class)
    {
        $className = $class->getName();
        if (!isset($this->hasClassMarker[$className]))
        {
            $markers = $this->getClassMarkers();
            $hasMarker = $this->hasMarker($className, $markers);
            $this->hasClassMarker[$className] = $hasMarker;
        }
        return (bool) $this->hasClassMarker[$className];
    }

    /**
     * @param  ReflectionClass $class
     * @return Marker 
     */
    public function getClassMarker(ReflectionClass $class)
    {
        if ($this->hasClassMarker($class))
        {
            $name = $class->getName();
            $markers = $this->getClassMarkers();
            foreach ($markers as $key => $value) {
                if ($value instanceof $name)
                {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getMethodsMarkers()
    {
        return (array) $this->methodMarkers;
    }

    /**
     * @param  string $method
     * @return array
     */
    public function getMethodMarkers($method)
    {
        if (!isset($this->methodMarkers[$method]))
        {
            throw new \InvalidArgumentException(
                    sprintf("Unknown method '%s' on class '%s'.", $method, $this->class->getName()));
        }
        return (array) $this->methodMarkers[$method];
    }

    /**
     * @param   ReflectionClass $class
     * @param   string $method
     * @return  bool
     */
    public function hasMethodMarker(ReflectionClass $class, $method)
    {
        $name = $class->getName();
        if (!isset($this->hasMethodMarker[$method][$name]))
        {
            $markers = $this->getMethodMarkers($method);
            $hasMarker = $this->hasMarker($class->getName(), $markers);
            $this->hasMethodMarker[$method][$name] = $hasMarker;
        }
        return (bool) $this->hasMethodMarker[$method][$name];
    }

    /**
     * @param  ReflectionClass $class
     * @param  string $method
     * @return Marker 
     */
    public function getMethodMarker(ReflectionClass $class, $method)
    {
        if ($this->hasMethodMarker($class, $method))
        {
            $name = $class->getName();
            $markers = $this->getMethodMarkers($method);
            foreach ($markers as $key => $value) {
                if ($value instanceof $name)
                {
                    return $value;
                }
            }
        }
        return null;
    }
    
    
     /**
     * @return array
     */
    public function getPropertiesMarkers()
    {
        return (array) $this->propertyMarkers;
    }

    /**
     * @param  string $property
     * @return array
     */
    public function getPropertyMarkers($property)
    {
        if (!isset($this->propertyMarkers[$property]))
        {
            throw new \InvalidArgumentException(
                    sprintf("Unknown property '%s' on class '%s'.", $property, $this->class->getName()));
        }
        return (array) $this->propertyMarkers[$property];
    }

    /**
     * @param   ReflectionClass $class
     * @param   string $property
     * @return  bool
     */
    public function hasPropertyMarker(ReflectionClass $class, $property)
    {
        $name = $class->getName();
        if (!isset($this->hasPropertyMarker[$property][$name]))
        {
            $markers    = $this->getPropertyMarkers($property);
            $hasMarker  = $this->hasMarker($class->getName(), $markers);
            $this->hasPropertyMarker[$property][$name] = $hasMarker;
        }
        return (bool) $this->hasPropertyMarker[$property][$name];
    }

    /**
     * @param  ReflectionClass $class
     * @param  string $property
     * @return Marker 
     */
    public function getPropertyMarker(ReflectionClass $class, $property)
    {
        if ($this->hasMethodMarker($class, $property))
        {
            $name    = $class->getName();
            $markers = $this->getPropertyMarkers($property);
            foreach ($markers as $key => $value) {
                if ($value instanceof $name)
                {
                    return $value;
                }
            }
        }
        return null;
    }

}
