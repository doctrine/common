Doctrine  Caching
=================

The Doctrine Common caching library was born from a need in the
`Doctrine2 ORM <http://www.doctrine-project.org/projects/orm>`_ to
allow caching of result sets. The library is independent and can be
used in your own libraries and applications to implement caching.

Introduction
------------

Doctrine Cache provides a very simple interface for which several out
of the box implementations are provided:

- ApcCache (requires ext/apc)
- ArrayCache (in memory, lifetime of the request)
- FilesystemCache (not optimal for high concurrency)
- MemcacheCache (requires ext/memcache)
- MemcachedCache (requires ext/memcached)
- PhpFileCache (not optimal for high concurrency)
- RedisCache.php (requires ext/phpredis)
- WinCacheCache.php (requires ext/wincache)
- XcacheCache.php (requires ext/xcache)
- ZendDataCache.php  (requires Zend Server Platform)

A simple code example using ``ArrayCache`` to cache data in memory for the lifetime
of the request can be found below.

.. code-block :: php

    <?php

    $cache = new \Doctrine\Common\Cache\ArrayCache();
    $data = $cache->fetch("some key");
    if ($data === false) {
        $data = do_something();
        $cache->save("some key", $data);
    }

    ..

    $cache->contains("some key");
    $cache->delete("some key");

    // not all cache backends provide stats, f.e. ArrayCache does not
    $stats = $cache->getStats();
    if (!empty($stats)) {
        foreach ($stats as $key => $value) {
            echo "$key: $value";
        }
    }
