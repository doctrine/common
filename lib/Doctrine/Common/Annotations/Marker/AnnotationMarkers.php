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
    private function getReader()
    {
        return $this->reader;
    }
    
    /**
     * @return \ReflectionClass 
     */
    private function getClass()
    {
        if($this->class->implementsInterface('Doctrine\Common\Annotations\Proxy\Proxy'))
        {
            $interface = $this->proxyClass($this->class);
            if($interface instanceof ReflectionClass)
            {
                return $interface;
            }
        }
        return $this->class;
    }

    /**
     * @param \ReflectionClass $class
     * @param Reader $reader 
     */
    public function __construct(\ReflectionClass $class, Reader $reader)
    {
        if($class->isSubclassOf('Doctrine\Common\Annotations\Marker\Annotation\Marker'))
        {
            throw new \InvalidArgumentException(
                sprintf("Annotation '%s' can not be a sub class of Marker.",$class->getName())
            );
        }
        
        if(!$class->implementsInterface('Doctrine\Common\Annotations\Marker\Marked'))
        {
            throw new \InvalidArgumentException(
                sprintf("Annotation '%s' not implements Marked.",$class->getName())
            );
        }
        
        $this->class = $class;
        $this->reader= $reader;

        $this->readMarkers();
    }

    
    /**
     * @param \ReflectionClass $class
     * @return \ReflectionClass
     */
    private function proxyClass(\ReflectionClass $class)
    {
        if($class->implementsInterface('Doctrine\Common\Annotations\Proxy\Proxy'))
        {
            foreach ($class->getInterfaces() as $interface) {
                if($interface->implementsInterface('Doctrine\Common\Annotations\Proxy\Proxyable'))
                {
                    return $interface;
                }
            }
        }
        return null;
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
        $this->sortByPriority($this->markers);
    }

    /**
     * Read annotation class markers
     */
    private function readClassMarkers()
    {
        $this->classMarkers     = array();
        $this->hasClassMarker   = array();
        $annotations            = $this->getReader()->getClassAnnotations($this->getClass());
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Marker)
            {
                $annotation->setClass($this->class);
                $this->addClassMarker($annotation);
            }
        }
        
        $this->sortByPriority($this->classMarkers);
    }

    /**
     * Read annotation method markers
     */
    private function readMethodMarkers()
    {
        $this->methodMarkers = array();
        $this->hasMethodMarker = array();
        $methods = $this->getClass()->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->getReader()->getMethodAnnotations($method);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Marker)
                {
                    $annotation->setClass($this->class);
                    $annotation->setMethod($this->class->getMethod($method->getName()));
                    $this->addMethodMarker($annotation, $method->getName());
                }
            }
            if(isset($this->methodMarkers[$method->getName()]))
            {
                $this->sortByPriority($this->methodMarkers[$method->getName()]);
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
        $properties = $this->getClass()->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->getReader()->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Marker)
                {
                    $annotation->setClass($this->class);
                    $annotation->setProperty($property);
                    $this->addPropertyMarker($annotation, $property->getName());
                }
            }
            if(isset($this->propertyMarkers[$property->getName()]))
            {
                $this->sortByPriority($this->propertyMarkers[$property->getName()]);
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
     * @param  array
     * @return array 
     */
    private function sortByPriority(array $markers)
    {
        usort($markers, function(Marker $a, Marker$b){
            if ($a->priority() == $b->priority()) {
                return 0;
            }
            return ($a->priority() < $b->priority()) ? -1 : 1;
        });
        return $markers;
    }

    /**
     * @return array
     */
    public function getAllMarkers()
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
     * @param   mixed $annotation
     * @param   mixed $target
     * @param   Marker $marker
     */
    public function runMarkers($annotation,\Reflector $target)
    {
        if(!($annotation instanceof $this->class->name))
        {
             throw new \InvalidArgumentException(
                sprintf('Argument 1 must be an instance of "%s", "%s" given. ',
                  $this->class->name, is_object($annotation) ? get_class($annotation) : gettext($annotation))
            );
        }
        
        foreach ($this->getAllMarkers() as $marker) {
            $this->runMarker($annotation, $target, $marker);
        }
    }
    
    /**
     * @param   mixed $annotation
     * @param   \Reflector $target
     * @param   Marker $marker
     */
    public function runMarker($annotation,\Reflector $target, Marker $marker)
    {
        if(!($annotation instanceof $this->class->name))
        {
             throw new \InvalidArgumentException(
                sprintf('Argument 1 must be an instance of "%s", "%s" given. ',
                  $this->class->name, is_object($annotation) ? get_class($annotation) : gettext($annotation))
            );
        }
        
        $strategy = MarkerStrategy::factory($this, $marker);
        if ($strategy instanceof MarkerStrategy)
        {
            $strategy->run($target,$annotation);
        }
    }

    /**
     * @return array
     */
    public function getClassMarkers()
    {
        return (array) $this->classMarkers;
    }

    /**
     * @param   string $name
     * @return  bool
     */
    public function hasClassMarker($name)
    {
        if (!isset($this->hasClassMarker[$name]))
        {
            $markers = $this->getClassMarkers();
            $hasMarker = $this->hasMarker($name, $markers);
            $this->hasClassMarker[$name] = $hasMarker;
        }
        return (bool) $this->hasClassMarker[$name];
    }

    /**
     * @param   string $name
     * @return  Marker 
     */
    public function getClassMarker($name)
    {
        if ($this->hasClassMarker($name))
        {
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
                sprintf("Unknown method '%s' on class '%s'.", $method, $this->class->getName())
            );
        }
        return (array) $this->methodMarkers[$method];
    }

    /**
     * @param   string $name
     * @param   string $method
     * @return  bool
     */
    public function hasMethodMarker($name, $method)
    {
        if (!isset($this->hasMethodMarker[$method][$name]))
        {
            $markers    = $this->getMethodMarkers($method);
            $hasMarker  = $this->hasMarker($name, $markers);
            $this->hasMethodMarker[$method][$name] = $hasMarker;
        }
        return (bool) $this->hasMethodMarker[$method][$name];
    }

    /**
     * @param  string $name
     * @param  string $method
     * @return Marker 
     */
    public function getMethodMarker($name, $method)
    {
        if ($this->hasMethodMarker($name, $method))
        {
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
     * @param   string $name
     * @param   string $property
     * @return  bool
     */
    public function hasPropertyMarker($name, $property)
    {
        if (!isset($this->hasPropertyMarker[$property][$name]))
        {
            $markers    = $this->getPropertyMarkers($property);
            $hasMarker  = $this->hasMarker($name, $markers);
            $this->hasPropertyMarker[$property][$name] = $hasMarker;
        }
        return (bool) $this->hasPropertyMarker[$property][$name];
    }

    /**
     * @param  string $name
     * @param  string $property
     * @return Marker 
     */
    public function getPropertyMarker($name, $property)
    {
        if ($this->hasPropertyMarker($name, $property))
        {
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
