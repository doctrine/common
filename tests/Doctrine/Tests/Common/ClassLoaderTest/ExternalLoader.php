<?php

namespace ClassLoaderTest;

class ExternalLoader
{
    public static function registerStatic() {
        spl_autoload_register(array('ClassLoaderTest\ExternalLoader', 'load1'));
        spl_autoload_register(array('ClassLoaderTest\ExternalLoader', 'load2'));
        spl_autoload_register('ClassLoaderTest\ExternalLoader::load3');
    }

    public static function unregisterStatic() {
        spl_autoload_unregister(array('ClassLoaderTest\ExternalLoader', 'load1'));
        spl_autoload_unregister(array('ClassLoaderTest\ExternalLoader', 'load2'));
        spl_autoload_unregister('ClassLoaderTest\ExternalLoader::load3');
    }

    public static function load1() {}

    protected static function load2() {}

    protected static function load3() {}

    public function register() {
        spl_autoload_register(array($this, 'load4'));
        spl_autoload_register(array($this, 'load5'));
    }

    public function unregister() {
        spl_autoload_unregister(array($this, 'load4'));
        spl_autoload_unregister(array($this, 'load5'));
    }

    public function load4() {}

    protected function load5() {}
}
