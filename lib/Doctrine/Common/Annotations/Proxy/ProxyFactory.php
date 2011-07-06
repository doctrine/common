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
use Doctrine\Common\Annotations\Factory;
use Doctrine\Common\Annotations\Proxy\Proxy;
use \ReflectionClass;

/**
 * FactoryProxy
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ProxyFactory
{
    const KEY_PROXY = 'proxy';
    const KEY_CLASS = 'class';
    
    const PROXY_INTERFACE       = 'Doctrine\Common\Annotations\Proxy\Proxy';
    const PROXYABLE_INTERFACE   = 'Doctrine\Common\Annotations\Proxy\Proxyable';
    

    /**
     * @var array 
     */
    private $_data      = array();
   
    /**
     * @var array 
     */
    private $map        = array();
    

    /**
     * @param   ReflectionClass $class
     * @return  bool 
     */
    public function hasProxy(ReflectionClass $class)
    {
        return array_key_exists($class->getName(),$this->map);
    }

    /**
     * @param   ReflectionClass $class
     * @return  bool 
     */
    public function isProxy(ReflectionClass $class)
    {
        return in_array($class->getName(),$this->map);
    }

    /**
     * @param ReflectionClass $class
     * @param ReflectionClass $proxy 
     */
    public function register(ReflectionClass $class,ReflectionClass $proxy)
    {
        if (!$class->isInterface())
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" is not a interface', $class->getName()));
        }
        
        if ($this->hasProxy($class))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" already has a proxy', $class->getName()));
        }

        if (!$proxy->implementsInterface($class->getName()))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" not implements "%s"', $proxy->getName(), $class->getName()));
        }

        $data   = array(
            self::KEY_PROXY => $proxy,
            self::KEY_CLASS => $class,
        );
        $this->_data[$class->getName()]  = $data;
        $this->map[$class->getName()]    = $proxy->getName();
    }

    /**
     * @param ReflectionClass $class
     */
    public function unregister(ReflectionClass $class)
    {
        if (!$this->hasProxy($class))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" is not registered', $class->getName()));
        }
        
        unset($this->_data[$class->getName()]);
        unset ($this->map[$class->getName()]);
    }

    /**
     * @param   ReflectionClass $class
     * @return  ReflectionClass
     */
    public function getProxyClass(ReflectionClass $class)
    {
        if (!$this->hasProxy($class))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" has no proxy', $class->getName()));
        }

        return $this->_data[$class->getName()][self::KEY_PROXY];
    }

    /**
     * @param   ReflectionClass $proxy
     * @return  ReflectionClass
     */
    public function getClass(ReflectionClass $proxy)
    {
        if (!$this->isProxy($proxy))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" is not a proxy', $proxy->getName()));
        }
        
        $key = array_search($proxy->getName(), $this->map);
        return $this->_data[$key][self::KEY_CLASS];
    }
    
    
    
     /**
     * @param   ReflectionClass $class
     * @return  ReflectionClass
     */
    public function proxy(ReflectionClass $class)
    {
        if (!$this->hasProxy($class))
        {
            return $this->generate($class);
        }

        return $this->_data[$class->getName()][self::KEY_PROXY];
    }

    /**
     * @param   ReflectionClass $class
     * @return  ReflectionClass
     */
    private function generate(ReflectionClass $class)
    {
        if (!$class->isInterface())
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" is not a interface', $class->getName()));
        }
        if (!$class->implementsInterface(self::PROXYABLE_INTERFACE))
        {
            throw new \InvalidArgumentException(
                    sprintf('Class "%s" not implements "%s"', $class->getName(),self::PROXYABLE_INTERFACE));
        }
        if ($this->hasProxy($class))
        {
            throw new \RuntimeException(
                    sprintf('Class "%s" is already generated', $class->getName()));
        }

        $proxyName          = $this->generateProxyName($class);
        $declaration        = $this->generateProxyDeclaration($proxyName, $class);
        $proxyName          = sprintf($class->getNamespaceName()."\\%s", $proxyName);
        
        eval($declaration);

        if(!class_exists($proxyName)){
            throw new \RuntimeException(
                    sprintf('Unable to generate proxy to "%s"', $class->getName()));
        }
        
        $proxy = new ReflectionClass($proxyName);
        

        $this->register($class, $proxy);
        
        return $proxy;
    }

    /**
     * @param  ReflectionClass $class
     * @return string
     */
    private function generateProxyName(ReflectionClass $class)
    {
        $proxyName      = $class->getShortName();
        do {
            $time       = substr(md5(microtime()), 0, 8);
            $proxyName .= sprintf("Proxy%s",$time);
        } while (class_exists($proxyName, false));

        return $proxyName;
    }


    /**
     * @param  array $methods
     * @return array
     */
    private function generateProxyMethodsImpl(array $methods)
    {
        $impl = array();
        foreach ($methods as $method) {
            $name   = $method->getName();
            $body   = sprintf('function %s(){return $this->%s;}', $name, $name);
            $impl[] = $body;
        }

        return $impl;
    }

    /**
     * @param  array $methods
     * @return array
     */
    private function generateProxyVars(array $methods)
    {
        $vars = array();
        foreach ($methods as $method) {
            $name   = $method->getName();
            $vars[] = sprintf('private $' . $name . ';', $name);
        }
        return $vars;
    }

    /**
     * @param  string $proxyName
     * @return string
     */
    private function generateProxyDeclaration($proxyName, \ReflectionClass $class)
    {
        $methods    = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->getParameters())
            {
                throw new \RuntimeException(
                        sprintf('Interface "%s" can not have parameters at function "%s"', 
                                $class->getName(), $method->getName()));
            }
        }

        $vars       = (array) $this->generateProxyVars($methods);
        $methods    = (array) $this->generateProxyMethodsImpl($methods);

        $vars       = implode(" ", $vars);
        $methods    = implode(" ", $methods);
        $implements = '\\'.$class->getName();
        $namespace  = $class->getNamespaceName();
        if (!$class->implementsInterface(self::PROXY_INTERFACE))
        {
            $implements = $implements . ', \\' . self::PROXY_INTERFACE;
        }

        $placeholders = array(
            '<namespace>', '<proxyName>',
            '<implements>', '<methods>', '<vars>'
        );

        $replacements = array(
            $namespace, $proxyName,
            $implements, $methods, $vars
        );

        return str_replace($placeholders, $replacements, self::$_proxyClassTemplate);
    }
    
    

    /** Proxy class code template */
    private static $_proxyClassTemplate =
            '
namespace <namespace>;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE.
 */
class <proxyName> implements <implements>
{
    <vars>
    
    <methods>
    
    public function __get($name)
    {
        if(!isset($this->$name))
        {
            throw new \BadMethodCallException(
                sprintf("Unknown property \'%s\' on annotation \'%s\'.", $name, get_called_class())
            );
        }
        return $this->$name;
    }
}';

}