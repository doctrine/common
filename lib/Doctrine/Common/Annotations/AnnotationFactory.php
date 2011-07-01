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

use Doctrine\Common\Annotations\Proxy\ProxyFactory;
use Doctrine\Common\Annotations\Proxy\ProxyDecorator;
use Doctrine\Common\Annotations\Factory;
use \ReflectionClass;

/**
 * Factory for annotations.
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class AnnotationFactory implements Factory
{

    /**
     * @var ReflectionClass
     */
    private $class;
    /**
     * @var ProxyFactory 
     */
    private $proxyFactory;
    /**
     * @var ProxyDecorator
     */
    private $decorator;
    /**
     * @var bool
     */
    private $isProxy = false;
    /**
     * @var ProxyFactory
     */
    private static $defaltProxyFactory;

    
    /**
     * @return ProxyFactory 
     */
    public static function getDefaltProxyFactory()
    {
        if (self::$defaltProxyFactory == null)
        {
            self::setDefaltProxyFactory(new ProxyFactory());
        }
        return self::$defaltProxyFactory;
    }
    
    /**
     * @return ProxyFactory 
     */
    public static function setDefaltProxyFactory(ProxyFactory $proxyFactory)
    {
        self::$defaltProxyFactory = $proxyFactory;
    }
    
     /**
     * @param ProxyFactory $proxyFactory 
     */
    public function setProxyFactory(ProxyFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }
    
    /**
     * @return ProxyFactory 
     */
    public function getProxyFactory()
    {
        if ($this->proxyFactory == null)
        {
            $this->setProxyFactory(self::getDefaltProxyFactory());
        }
        return $this->proxyFactory;
    }
    
    
    /**
     * @param ReflectionClass $class
     */
    public function __construct(\ReflectionClass $class)
    {
        $this->setAnnotationClass($class);
    }

    
    /**
     * @param \ReflectionClass $class 
     */
    public function setAnnotationClass(\ReflectionClass $class)
    {
        $this->class    = $class;
        $this->isProxy  = $class->isInterface();
        
        if (!$this->isAnnotation())
        {
            throw AnnotationException::semanticalError(sprintf('The class "%s" is not an annotation.', $class->getName()));
        }
        
        if ($this->isProxy)
        {
            $class = $this->getProxyFactory()->proxy($class);
        }
        
        $this->decorator = new ProxyDecorator($class);
    }

    /**
     * @return mixed
     */
    public function newAnnotation(array $data = array())
    {
        if ($this->isProxy)
        {
            $class      = $this->getProxyFactory()->proxy($this->class);
            $annotation = $class->newInstance();
            $this->decorator->setData($annotation, $data);
        }
        else{
            $class      = $this->getClassName();
            $annotation = new $class($data);
        }
        return $annotation;
    }
    
    /**
     * @return  bool 
     */
    public function isAnnotation()
    {
        //TODO - check if all classes can be annotation
        return true;
        //return $this->class->implementsInterface(Factory::ANNOTATION_INTERFACE);
    }

    /**
     * @return string The class name.
     */
    public function getClassName()
    {
        return $this->class->getName();
    }

}
