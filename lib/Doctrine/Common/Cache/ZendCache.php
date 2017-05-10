<?php

namespace Doctrine\Common\Cache;

use Doctrine\Common\Cache\CacheProvider;

class ZendCache extends CacheProvider
{

    /**
     * @var \Zend_Cache_Core
     */
    private $_cache;

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public function initialize($config = array())
    {
        $config = (array_key_exists('options', $config)) ? $config['options'] : array();
        if (!array_key_exists('frontendAdapter', $config)) {
            throw new \InvalidArgumentException('Frontend adapter for Zend cache not found');
        }
        if (!array_key_exists('backendAdapter', $config)) {
            throw new \InvalidArgumentException('Backend adapter for Zend cache not found');
        }
        $frontendOptions = (array_key_exists('frontend', $config)) ? $config['frontend'] : array();
        $backendOptions = (array_key_exists('backend', $config)) ? $config['backend'] : array();
        $cache = \Zend_Cache::factory($config['frontendAdapter'], $config['backendAdapter'], $frontendOptions, $backendOptions);
        $this->setCache($cache);
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     * @return string The cached data or FALSE, if no cache entry exists for the given id.
     */
    protected function doFetch($id)
    {
        if (!($id = self::_validateAndFixId($id))) {
            throw new \InvalidArgumentException('Invalid ID "' . $id . '" passed');
        }
        return $this->_cache->load($id);
    }

    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id cache id The cache id of the entry to check for.
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    protected function doContains($id)
    {
        if (!($id = self::_validateAndFixId($id))) {
            throw new \InvalidArgumentException('Invalid ID "' . $id . '" passed');
        }
        return $this->_cache->test($id);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string $data The cache entry/data.
     * @param int $lifeTime The lifetime. If != false, sets a specific lifetime for this cache entry (null => infinite lifeTime).
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        if (!($id = self::_validateAndFixId($id))) {
            throw new \InvalidArgumentException('Invalid ID "' . $id . '" passed');
        }
        $tags = ('' !== $this->getNamespace()) ? array($this->getNamespace()) : array();
        return $this->_cache->save($data, $id, $tags, $lifeTime);
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    protected function doDelete($id)
    {
        if (!($id = self::_validateAndFixId($id))) {
            throw new \InvalidArgumentException('Invalid ID "' . $id . '" passed');
        }
        return $this->_cache->remove($id);
    }

    /**
     * Deletes all cache entries.
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    protected function doFlush()
    {
        if ('' !== $this->getNamespace()) {
            return $this->_cache->clean('matchingTag', array($this->getNamespace()));
        }
        return $this->_cache->clean();
    }

    /**
     * Retrieves cached information from data store
     *
     * @since   2.2
     * @return  array An associative array with server's statistics if available, NULL otherwise.
     */
    protected function doGetStats()
    {
        return null;
    }

    protected static function _validateAndFixId($id)
    {
        if (!is_string($id)) {
            try {
                $id = serialize($id);
            }
            catch (\Exception $e) {
                return false;
            }
        }
        if (substr($id, 0, 9) == 'internal-') {
            $id = 'doctrine-' . $id;
        }
        if (!preg_match('~^[a-zA-Z0-9_]+$~D', $id)) {
            $id = md5($id);
        }
        return $id;
    }

    /**
     * @param \Zend_Cache_Core $cache
     * @return Zend
     */
    public function setCache(\Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * @return \Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_cache;
    }

}