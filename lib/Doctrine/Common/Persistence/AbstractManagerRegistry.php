<?php
namespace Doctrine\Common\Persistence;

use Doctrine\Common\Util\ClassUtils;

/**
 * Abstract implementation of the ManagerRegistry contract.
 *
 * @link   www.doctrine-project.org
 * @since  2.2
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
abstract class AbstractManagerRegistry implements ManagerRegistry
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $connections;

    /**
     * @var array
     */
    private $managers;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $defaultManager;

    /**
     * @var string
     */
    private $proxyInterfaceName;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $connections
     * @param array  $managers
     * @param string $defaultConnection
     * @param string $defaultManager
     * @param string $proxyInterfaceName
     */
    public function __construct($name, array $connections, array $managers, $defaultConnection, $defaultManager, $proxyInterfaceName)
    {
        $this->name               = $name;
        $this->connections        = $connections;
        $this->managers           = $managers;
        $this->defaultConnection  = $defaultConnection;
        $this->defaultManager     = $defaultManager;
        $this->proxyInterfaceName = $proxyInterfaceName;
    }

    /**
     * Fetches/creates the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return object The instance of the given service.
     */
    abstract protected function getService($name);

    /**
     * Resets the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return void
     */
    abstract protected function resetService($name);

    /**
     * Gets the name of the registry.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($name = null)
    {
        if (null === $name) {
            $name = $this->defaultConnection;
        }

        if ( ! isset($this->connections[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine %s Connection named "%s" does not exist.', $this->name, $name));
        }

        return $this->getService($this->connections[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
        return $this->connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
        $connections = [];
        foreach ($this->connections as $name => $id) {
            $connections[$name] = $this->getService($id);
        }

        return $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName()
    {
        return $this->defaultManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function getManager($name = null)
    {
        if (null === $name) {
            $name = $this->defaultManager;
        }

        if ( ! isset($this->managers[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name));
        }

        return $this->getService($this->managers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass($class)
    {
        // Check for namespace alias
        if (ClassUtils::isClassNameAliasedClassName($class)) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class, 2);
            $class                                  = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new \ReflectionClass($class);

        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            if ( ! $parentClass = $proxyClass->getParentClass()) {
                return null;
            }

            $class = $parentClass->getName();
        }

        foreach ($this->managers as $id) {
            $manager = $this->getService($id);

            if ( ! $manager->getMetadataFactory()->isTransient($class)) {
                return $manager;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerNames()
    {
        return $this->managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers()
    {
        $dms = [];
        foreach ($this->managers as $name => $id) {
            $dms[$name] = $this->getService($id);
        }

        return $dms;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($persistentObjectName, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObjectName);
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null)
    {
        if (null === $name) {
            $name = $this->defaultManager;
        }

        if ( ! isset($this->managers[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name));
        }

        // force the creation of a new document manager
        // if the current one is closed
        $this->resetService($this->managers[$name]);

        return $this->getManager($name);
    }
}
