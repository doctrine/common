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

namespace Doctrine\Common\Annotations\Proxy;

/**
 * ProxyDecorator
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ProxyDecorator
{
    
    /**
     * @var string 
     */
    protected $class;
    
    /**
     * @var \ReflectionClass 
     */
    protected $reflectionClass;
    
    /**
     * @var array 
     */
    protected $methods = null;
    
    /**
     * @var array 
     */
    protected $methodExists = array();
    
    /**
     * @var array 
     */
    protected static $instances = array();

    /**
     * const
     */
    const DEFAULT_KEY = 'value';

    /**
     * @param   string $class
     * @return  Decorator 
     */
    public static function getInstance($class)
    {
        if (!isset(self::$instances[$class]))
        {
            self::$instances[$class] = new self($class);
        }
        return self::$instances[$class];
    }

    /**
     * Constructor
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->reflectionClass = new \ReflectionClass($class);
    }

    /**
     * @param AbstractProxy $proxy
     * @param array $data 
     */
    public function setData(AbstractProxy $proxy, array $data)
    {
        if (!empty($data))
        {
            foreach ($data as $name => $value)
            {
                if (!$this->methodExists($name))
                {
                    if ($name == self::DEFAULT_KEY)
                    {
                        $methods    = $this->getMethods();
                        $name       = reset($methods);
                        $this->setPropertyValue($proxy, $name, $value);
                    } 
                    else
                    {
                        throw new \BadMethodCallException(
                                sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
                        );
                    }
                }
                $this->setPropertyValue($proxy, $name, $value);
            }
        }
    }

    /**
     * @param type $method
     * @return type 
     */
    public function methodExists($method)
    {
        if (!isset($this->methodExists[$method]))
        {
            $this->methodExists[$method] = method_exists($this->class, $method);
        }
        return $this->methodExists[$method];
    }

    /**
     * @return array 
     */
    public function getMethods()
    {
        if (is_null($this->methods))
        {
            $this->methods = get_class_methods($this->class);
        }
        return (array) $this->methods;
    }

    /**
     * @param   mixed     $instance
     * @param   string    $property
     * @param   mixed     $value
     * @return  mixed
     */
    protected function setPropertyValue($instance, $property, $value)
    {
        $reflection = $this->reflectionClass;
        $setterMethod = 'set' . ucfirst($property);
        if ($reflection->hasProperty($property))
        {
            $propertyObj = $reflection->getProperty($property);
            if ($propertyObj->isPublic())
            {
                $instance->{$property} = $value;
            } 
            else if ($reflection->hasMethod($setterMethod))
            {
                $instance->{$setterMethod}($value);
            } 
            else
            {
                $propertyObj->setAccessible(true);
                $propertyObj->setValue($instance, $value);
                $propertyObj->setAccessible(false);
            }
        } 
        else if (get_class($instance) == 'stdClass')
        {
            $instance->{$property} = $value;
        }
        return $instance;
    }

}