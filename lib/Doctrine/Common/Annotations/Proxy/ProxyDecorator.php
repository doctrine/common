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

use Doctrine\Common\Annotations\Annotation\Annotation;

/**
 * ProxyDecorator
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ProxyDecorator
{
    /**
     * const
     */
    const DEFAULT_KEY = 'value';
    /**
     * @var string 
     */
    private $class;
    /**
     * @var array 
     */
    private $properties = null;
    /**
     * @var array 
     */
    private $hasMethod = array();
    /**
     * @var array 
     */
    private $hasProperty = array();

    /**
     * Constructor
     */
    public function __construct(\ReflectionClass $class)
    {
        $this->class = $class;
    }

    /**
     * @param Annotation $proxy
     * @param array $data 
     */
    public function setData(Annotation $proxy, array $data)
    {
        if (!empty($data))
        {

            foreach ($data as $name => $value) {
                if (!$this->hasProperty($name))
                {

                    if ($name == self::DEFAULT_KEY)
                    {
                        $methods    = $this->getProperties();
                        $name       = reset($methods);

                        $this->setPropertyValue($proxy, $name, $value);
                    } else
                    {
                        throw new \BadMethodCallException(
                                sprintf("Unknown property '%s' on annotation '%s'.", $name, $this->class->getName())
                        );
                    }
                }else{
                    $this->setPropertyValue($proxy, $name, $value);   
                }
            }
        }
    }

    /**
     * @param   string $name
     * @return  bool
     */
    private function hasProperty($name)
    {
        if (!isset($this->hasProperty[$name]))
        {
            $this->hasProperty[$name] = $this->class->hasProperty($name);
        }
        return $this->hasProperty[$name];
    }

    /**
     * @return  array
     */
    private function getProperties()
    {
        if (empty($this->properties))
        {
            $list = (array)$this->class->getProperties();
            foreach ($list as $property) {
                $this->properties[] = $property->getName();
            }
        }
        return $this->properties;
    }

    /**
     * @param   mixed     $instance
     * @param   string    $property
     * @param   mixed     $value
     * @return  mixed
     */
    private function setPropertyValue($instance, $property, $value)
    {
        $prop  = $this->class->getProperty($property);

        if ($prop->isPublic())
        {
            $instance->{$property} = $value;
        } else
        {
            $prop->setAccessible(true);
            $prop->setValue($instance, $value);
            $prop->setAccessible(false);
        }
        return $instance;
    }

}