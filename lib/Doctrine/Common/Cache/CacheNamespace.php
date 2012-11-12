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

namespace Doctrine\Common\Cache;

/**
 * Provides non conflicting access to a common cache instance.
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class CacheNamespace implements Cache
{
    /** @var \Doctrine\Common\Cache\Cache */
    protected $cache;

    /**
     * Prefix for all cache keys
     * @var string
     */
    protected $namespace;

    /** @var int Current Namespace version */
    protected $namespaceVersion;

    /** @const Key to store the namespace's version */
    const NAMESPACE_CACHE_KEY = "DoctrineCacheNamespaceVersion[%s]";

    /**
     * Constructor
     *
     * @param string $namespace
     * @param Cache  $cache
     */
    public function __construct($namespace, Cache $cache)
    {
        $this->namespace = (string) $namespace;
        $this->cache = $cache;
    }

    /**
     * Returns the namespace.
     *
     * @return string
     */
    function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    function contains($id)
    {
        return $this->cache->contains($this->getNamespaceId($id));
    }

    /**
     * {@inheritdoc}
     */
    function fetch($id)
    {
        return $this->cache->fetch($this->getNamespaceId($id));
    }

    /**
     * {@inheritdoc}
     */
    function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save(
            $this->getNamespaceId($id),
            $data,
            $lifeTime
        );
    }

    /**
     * {@inheritdoc}
     */
    function delete($id)
    {
        return $this->cache->delete($this->getNamespaceId($id));
    }

    /**
     * {@inheritdoc}
     */
    function getStats()
    {
        return $this->cache->getStats();
    }

    /**
     * Increments the namespace version, invalidating the namespace.
     *
     * @return void
     */
    function incrementNamespaceVersion()
    {
        $version = $this->getNamespaceVersion();
        $version += 1;

        $this->namespaceVersion = $version;

        $this->cache->save($this->getNamespaceCacheKey($this->namespace), $this->namespaceVersion);
    }

    /**
     * Prefixes the cache ID with the namespace and namespace version.
     *
     * @param mixed $id Cache ID
     * @return string
     */
    protected function getNamespaceId($id)
    {
        return sprintf("%s[%s][%s]", $this->namespace, $id, $this->getNamespaceVersion());
    }

    /**
     * Returns the cache key which contains the namespace's version
     *
     * @param string $namespace
     * @return string
     */
    protected function getNamespaceCacheKey($namespace)
    {
        return sprintf(self::NAMESPACE_CACHE_KEY, $namespace);
    }

    protected function getNamespaceVersion()
    {
        if (null !== $this->namespaceVersion) {
            return $this->namespaceVersion;
        }

        $namespaceCacheKey = $this->getNamespaceCacheKey($this->namespace);
        $namespaceVersion = $this->cache->fetch($namespaceCacheKey);

        if (false === $namespaceVersion) {
            $namespaceVersion = 1;
            $this->cache->save($namespaceCacheKey, $namespaceVersion);
        }

        $this->namespaceVersion = $namespaceVersion;

        return $this->namespaceVersion;
    }
}
