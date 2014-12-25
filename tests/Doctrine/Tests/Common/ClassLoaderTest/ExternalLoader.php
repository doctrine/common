<?php

namespace ClassLoaderTest;

class ExternalLoader
{
    public static function register() {
        spl_autoload_register(array('ClassLoaderTest\ExternalLoader', 'load'));
        spl_autoload_register(array('ClassLoaderTest\ExternalLoader', 'load2'));
        spl_autoload_register('ClassLoaderTest\ExternalLoader::load3');
    }

    public static function unregister() {
        spl_autoload_unregister(array('ClassLoaderTest\ExternalLoader', 'load'));
        spl_autoload_unregister(array('ClassLoaderTest\ExternalLoader', 'load2'));
        spl_autoload_unregister('ClassLoaderTest\ExternalLoader::load3');
    }

    public static function load() {}

    protected static function load2() {}

    protected static function load3() {}
}
