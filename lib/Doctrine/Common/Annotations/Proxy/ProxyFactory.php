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

use  Doctrine\Common\Annotations\Proxy\AbstractProxy;

/**
 * FactoryProxy
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ProxyFactory
{
    const PROXY_BASE            = 'Doctrine\\Common\\Annotations\\Proxy\AbstractProxy';
    const ANNOTATION_INTERFACE  = 'Doctrine\\Common\\Annotations\\Annotation\\Annotation';

    /**
     * @var array 
     */
    private $impl = array();


    /**
     * @param   string $interface
     */
    protected function generate($interface)
    {
        if (!interface_exists($interface))
        {
            throw new \InvalidArgumentException(
                    sprintf('Interface "%s" not found', $interface));
        }
        if ($this->isRegistered($interface))
        {
            throw new \RuntimeException(
                    sprintf('class "%s" is already generated', $interface));
        }

        if ($interface[0] == '\\')
        {
            $interface = substr($interface, 1);
        }

        $class          = $this->generateProxyClassName($interface);
        $declaration    = $this->generateProxyClassDeclaration($class, $interface);
        $impl           =  __NAMESPACE__ . "\\" . $class;
        
        eval($declaration);
        
        $this->register($interface, $impl);
    }

    /**
     * @param  string $interface
     * @return array
     */
    protected function generateProxyClassName($class)
    {
        $class = $this->getSimpleClassName($class);

        do
        {
            $item = substr(md5(microtime()), 0, 8);
            $class = sprintf("%sProxy%s", $class, $item);
        } while (class_exists($class, false));

        return $class;
    }

    /**
     * @param   string $class
     * @return  string
     */
    private function getSimpleClassName($class)
    {
        $name = explode('\\', $class);
        return $name[count($name)-1];
    }

    /**
     * @param  string $class
     * @return string
     */
    protected function generateProxyClassDeclaration($class, $interface)
    {
        $ref        = new \ReflectionClass($interface);
        $methods    = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        $interfaces = $ref->getInterfaceNames();
        $impl       = array();

        foreach ($methods as $method)
        {
            if ($method->getParameters())
            {
                throw new \RuntimeException(
                        sprintf('Interface "%s" can not have parameters at function "%s"', $interface,$method->getName()));
            }
        }
        
        $vars       = (array) $this->generateProxyVars($methods);
        $methods    = (array) $this->generateProxyMethodsImpl($methods);
        
        $vars       = implode(" ", $vars);
        $methods    = implode(" ", $methods);
        $interface  = "\\$interface";
        if(!in_array(self::ANNOTATION_INTERFACE, $interfaces))
        {
            $interface = $interface .', \\' . self::ANNOTATION_INTERFACE;
        }
        
        $placeholders = array(
            '<namespace>', '<className>','<baseClassName>',
            '<implements>','<methods>', '<vars>'
        );

        $replacements = array(
            __NAMESPACE__,$class, self::PROXY_BASE,
            $interface, $methods, $vars
        );

        return str_replace($placeholders, $replacements, self::$_proxyClassTemplate);
    }

    /**
     * @param  array $methods
     * @return string
     */
    protected function generateProxyMethodsImpl(array $methods)
    {
        $impl = array();
        foreach ($methods as $method)
        {
            $name = $method->getName();
            $body = sprintf('function %s(){return $this->%s;}', $name, $name);
            $impl[] = $body;
        }

        return $impl;
    }

    
    /**
     * @param  array $methods
     * @return string
     */
    protected function generateProxyVars(array $methods)
    {
        $vars = array();
        foreach ($methods as $method)
        {
            $name   = $method->getName();
            $vars[] = sprintf('private $'.$name.';', $name);
        }
        return $vars;
    }

    /**
     * @param string $interface
     * @return bool 
     */
    public function isRegistered($interface)
    {
        return array_key_exists($interface, $this->impl);
    }

    /**
     * @param string $interface
     * @param string $impl 
     */
    public function register($interface, $impl)
    {
        if (isset($this->impl[$interface]))
        {
            throw new \InvalidArgumentException(
                    sprintf('Interface "%s" is already registered', $interface));
        }

        if (!interface_exists($interface))
        {
            throw new \InvalidArgumentException(
                    sprintf('Interface "%s" not found', $interface));
        }

        if (!class_exists($impl))
        {
            throw new \InvalidArgumentException(
                    sprintf('class "%s" not found', $impl));
        }

        if (!in_array($interface, class_implements($impl)))
        {
            throw new \InvalidArgumentException(
                    sprintf('class "%s" not implements "%s"', $impl, $interface));
        }
        
        if (!in_array(self::PROXY_BASE, class_parents($impl)))
        {
            throw new \InvalidArgumentException(
                    sprintf('class "%s" not extends "%s"', $impl, self::PROXY_BASE));
        }
        
        $this->impl[$interface] = $impl;
    }
    
    /**
     * @param string $interface
     */
    public function unregister($interface)
    {
        if (!isset($this->impl[$interface]))
        {
            throw new \InvalidArgumentException(
                    sprintf('Interface "%s" is not registered', $interface));
        }
        unset ($this->impl[$interface]);
    }
    
    

    /**
     * @param string $interface
     * @return strign
     */
    public function getImplClass($interface)
    {
        if (!$this->isRegistered($interface))
        {
            $this->generate($interface);
        }

        return $this->impl[$interface];
    }
    
    
    
    /**
     * @param string $class
     * @return strign
     */
    public function getInterface($class)
    {
        return array_search($class, $this->impl);
    }
    
    
    
    /** Proxy class code template */
    private static $_proxyClassTemplate =
'
namespace <namespace>;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE.
 */
class <className> extends \<baseClassName> implements <implements>
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