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
use \ReflectionClass;

/**
 * Factory for annotations.
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class AnnotationFactory
{

    /**
     * @var ReflectionClass
     */
    private $class;
    /**
     * @var ProxyFactory
     */
    private static $proxyFactory;

    /**
     * @param ReflectionClass $class
     */
    public function __construct(ReflectionClass $class)
    {
        $this->setAnnotationClass($class);
    }

    /**
     * @return ProxyFactory 
     */
    public static function getProxyFactory()
    {
        if (self::$proxyFactory == null)
        {
            self::$proxyFactory = new ProxyFactory();
        }
        return self::$proxyFactory;
    }

    /**
     * @param \ReflectionClass $class 
     */
    function setAnnotationClass(\ReflectionClass $class)
    {
        $this->class = $class;

        if (!$this->isAnnotation())
        {
            throw AnnotationException::semanticalError(sprintf('The class "%s" is not an annotation.', $this->getClassName()));
        }
    }

    /**
     * @return mixed
     */
    public function newAnnotation(array $data = array())
    {
        $class = $this->class->getName();
        if ($this->class->isInterface())
        {
            $class = self::getProxyFactory()->getImplClass($class);
        }

        return new $class($data);
    }

    /**
     * @return  bool 
     */
    public function isAnnotation()
    {
        // now all class or interface can be annotation
        return true;

        if (!in_array($this->getClassName(), class_implements(ProxyFactory::ANNOTATION_INTERFACE)))
        {
            return true;
        }
    }

    /**
     * @return string The class name.
     */
    public function getClassName()
    {
        return $this->class->getName();
    }

}
