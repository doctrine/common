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

namespace Doctrine\Common\Persistence\Mapping;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping informations of a class which describes how a class should be mapped
 * to a relational database.
 *
 * This class was abstracted from the ORM ClassMetadataFactory
 *
 * @since   2.2
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
abstract class AbstractClassMetadataFactory implements ClassMetadataFactory
{
    /**
     * Salt used by specific Object Manager implementation.
     *
     * @var string
     */
    protected $cacheSalt = "\$CLASSMETADATA";

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cacheDriver;

    /**
     * @var array
     */
    private $loadedMetadata = array();

    /**
     * @var bool
     */
    protected $initialized = false;
    
    /**
     * Sets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @param Doctrine\Common\Cache\Cache $cacheDriver
     */
    public function setCacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }

    /**
     * Gets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @return Doctrine\Common\Cache\Cache
     */
    public function getCacheDriver()
    {
        return $this->cacheDriver;
    }

    /**
     * Return an array of all the loaded metadata currently in memory.
     * 
     * @return array
     */
    public function getLoadedMetadata()
    {
        return $this->loadedMetadata;
    }

    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }
        
        $driver = $this->getDriver();
        $metadata = array();
        foreach ($driver->getAllClassNames() as $className) {
            $metadata[] = $this->getMetadataFor($className);
        }

        return $metadata;
    }

    /**
     * Lazy initialization of this stuff, especially the metadata driver,
     * since these are not needed at all when a metadata cache is active.
     * 
     * @return void
     */
    abstract protected function initialize();

    /**
     * Get the fully qualified class-name from the namespace alias.
     * 
     * @param string $namespaceAlias
     * @param string $simpleClassName
     * @return string
     */
    abstract protected function getFqcnFromAlias($namespaceAlias, $simpleClassName);
    
    /**
     * Return the mapping driver implementation.
     * 
     * @return MappingDriver
     */
    abstract protected function getDriver();

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     * @return Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getMetadataFor($className)
    {
        if ( ! isset($this->loadedMetadata[$className])) {
            $realClassName = $className;

            // Check for namespace alias
            if (strpos($className, ':') !== false) {
                list($namespaceAlias, $simpleClassName) = explode(':', $className);
                $realClassName = $this->getFqcnFromAlias($namespaceAlias, $simpleClassName);

                if (isset($this->loadedMetadata[$realClassName])) {
                    // We do not have the alias name in the map, include it
                    $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];

                    return $this->loadedMetadata[$realClassName];
                }
            }

            if ($this->cacheDriver) {
                if (($cached = $this->cacheDriver->fetch($realClassName . $this->cacheSalt)) !== false) {
                    $this->loadedMetadata[$realClassName] = $cached;
                } else {
                    foreach ($this->loadMetadata($realClassName) as $loadedClassName) {
                        $this->cacheDriver->save(
                            $loadedClassName . $this->cacheSalt, $this->loadedMetadata[$loadedClassName], null
                        );
                    }
                }
            } else {
                $this->loadMetadata($realClassName);
            }

            if ($className != $realClassName) {
                // We do not have the alias name in the map, include it
                $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];
            }
        }

        return $this->loadedMetadata[$className];
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param string $className
     * @return boolean TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     *
     * @param string $className
     * @param ClassMetadata $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->loadedMetadata[$className] = $class;
    }

    /**
     * Get array of parent classes for the given entity class
     *
     * @param string $name
     * @return array $parentClasses
     */
    protected function getParentClasses($name)
    {
        // Collect parent classes, ignoring transient (not-mapped) classes.
        $parentClasses = array();
        foreach (array_reverse(class_parents($name)) as $parentClass) {
            if ( ! $this->getDriver()->isTransient($parentClass)) {
                $parentClasses[] = $parentClass;
            }
        }
        return $parentClasses;
    }

    /**
     * Loads the metadata of the class in question and all it's ancestors whose metadata
     * is still not loaded.
     *
     * @param string $name The name of the class for which the metadata should get loaded.
     * @param array  $tables The metadata collection to which the loaded metadata is added.
     */
    protected function loadMetadata($name)
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }

        $loaded = array();

        $parentClasses = $this->getParentClasses($name);
        $parentClasses[] = $name;

        // Move down the hierarchy of parent classes, starting from the topmost class
        $parent = null;
        $rootEntityFound = false;
        $visited = array();
        foreach ($parentClasses as $className) {
            if (isset($this->loadedMetadata[$className])) {
                $parent = $this->loadedMetadata[$className];
                if (isset($parent->isMappedSuperclass) && $parent->isMappedSuperclass === false) {
                    $rootEntityFound = true;
                    array_unshift($visited, $className);
                }
                continue;
            }

            $class = $this->newClassMetadataInstance($className);

            $this->doLoadMetadata($class, $parent, $rootEntityFound);

            $this->loadedMetadata[$className] = $class;

            $parent = $class;

            if (isset($parent->isMappedSuperclass) && $class->isMappedSuperclass === false) {
                $rootEntityFound = true;
                array_unshift($visited, $className);
            }

            $loaded[] = $className;
        }

        return $loaded;
    }

    /**
     * Actually load the metadata from the underlying metadata
     * 
     * @param ClassMetadata $class
     * @param ClassMetadata $parent
     * @param bool $rootEntityFound
     * @return void
     */
    abstract protected function doLoadMetadata($class, $parent, $rootEntityFound);

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return ClassMetadata
     */
    abstract protected function newClassMetadataInstance($className);

    /**
     * Check if this class is mapped by this Object Manager + ClassMetadata configuration
     *
     * @param $class
     * @return bool
     */
    public function isTransient($class)
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }
        
        return $this->getDriver()->isTransient($class);
    }
}
