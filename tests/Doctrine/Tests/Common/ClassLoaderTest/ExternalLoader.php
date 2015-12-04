<?php

namespace ClassLoaderTest;

class ExternalLoader
{
    public static function registerStatic() {
        spl_autoload_register([ExternalLoader::class, 'load1']);
        spl_autoload_register([ExternalLoader::class, 'load2']);
        spl_autoload_register(ExternalLoader::class . '::load3');
    }

    public static function unregisterStatic() {
        spl_autoload_unregister([ExternalLoader::class, 'load1']);
        spl_autoload_unregister([ExternalLoader::class, 'load2']);
        spl_autoload_unregister(ExternalLoader::class . '::load3');
    }

    public static function load1() {}

    protected static function load2() {}

    protected static function load3() {}

    public function register() {
        spl_autoload_register([$this, 'load4']);
        spl_autoload_register([$this, 'load5']);
    }

    public function unregister() {
        spl_autoload_unregister([$this, 'load4']);
        spl_autoload_unregister([$this, 'load5']);
    }

    public function load4() {}

    protected function load5() {}
}
