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

namespace Doctrine\Common\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Abstract factory for proxy objects.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class AbstractProxyFactory
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var \Doctrine\Common\Proxy\ProxyGenerator the proxy generator responsible for creating the proxy classes/files.
     */
    private $proxyGenerator;

    /**
     * @var bool Whether to automatically (re)generate proxy classes.
     */
    private $autoGenerate;

    /**
     * @var \Doctrine\Common\Proxy\ProxyDefinition[]
     */
    private $definitions = array();

    /**
     * @param \Doctrine\Common\Proxy\ProxyGenerator                     $proxyGenerator
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $metadataFactory
     * @param bool                                                      $autoGenerate
     */
    public function __construct(ProxyGenerator $proxyGenerator, ClassMetadataFactory $metadataFactory, $autoGenerate)
    {
        $this->proxyGenerator  = $proxyGenerator;
        $this->metadataFactory = $metadataFactory;
        $this->autoGenerate    = $autoGenerate;
    }

    /**
     * Gets a reference proxy instance for the entity of the given type and identified by
     * the given identifier.
     *
     * @param  string $className
     * @param  array  $identifier
     *
     * @return \Doctrine\Common\Proxy\Proxy
     */
    public function getProxy($className, array $identifier)
    {
        $definition = isset($this->definitions[$className])
            ? $this->definitions[$className]
            : $this->getProxyDefinition($className);
        $fqcn       = $definition->proxyClassName;
        $proxy      = new $fqcn($definition->initializer, $definition->cloner);

        foreach ($definition->identifierFields as $idField) {
            $definition->reflectionFields[$idField]->setValue($proxy, $identifier[$idField]);
        }

        return $proxy;
    }

    /**
     * Generates proxy classes for all given classes.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata[] $classes The classes (ClassMetadata instances)
     *                                                                      for which to generate proxies.
     * @param string $proxyDir The target directory of the proxy classes. If not specified, the
     *                         directory configured on the Configuration of the EntityManager used
     *                         by this factory is used.
     * @return int Number of generated proxies.
     */
    public function generateProxyClasses(array $classes, $proxyDir = null)
    {
        $generated = 0;

        foreach ($classes as $class) {
            if ($this->skipClass($class)) {
                continue;
            }

            $proxyFileName = $this->proxyGenerator->getProxyFileName($class->getName(), $proxyDir);

            $this->proxyGenerator->generateProxyClass($class, $proxyFileName);

            $generated += 1;
        }

        return $generated;
    }

    /**
     * Reset initialization/cloning logic for an un-initialized proxy
     *
     * @param \Doctrine\Common\Proxy\Proxy $proxy
     *
     * @return \Doctrine\Common\Proxy\Proxy
     *
     * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
     */
    public function resetUninitializedProxy(Proxy $proxy)
    {
        if ($proxy->__isInitialized()) {
            throw InvalidArgumentException::unitializedProxyExpected($proxy);
        }

        $className  = ClassUtils::getClass($proxy);
        $definition = isset($this->definitions[$className])
            ? $this->definitions[$className]
            : $this->getProxyDefinition($className);

        $proxy->__setInitializer($definition->initializer);
        $proxy->__setCloner($definition->cloner);

        return $proxy;
    }

    /**
     * Get a proxy definition for the given class name.
     *
     * @return ProxyDefinition
     */
    private function getProxyDefinition($className)
    {
        $classMetadata = $this->metadataFactory->getMetadataFor($className);
        $className     = $classMetadata->getName(); // aliases and case sensitivity

        $this->definitions[$className] = $this->createProxyDefinition($className);
        $proxyClassName                = $this->definitions[$className]->proxyClassName;

        if ( ! class_exists($proxyClassName, false)) {
            $fileName  = $this->proxyGenerator->getProxyFileName($className);

            if ($this->autoGenerate) {
                $this->proxyGenerator->generateProxyClass($classMetadata);
            }

            require $fileName;
        }

        return $this->definitions[$className];
    }

    /**
     * Determine if this class should be skipped during proxy generation.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @return bool
     */
    abstract protected function skipClass(ClassMetadata $metadata);

    /**
     * @return ProxyDefinition
     */
    abstract protected function createProxyDefinition($className);
}

