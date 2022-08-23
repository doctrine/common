<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\Autoloader;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use const DIRECTORY_SEPARATOR;
use function class_exists;
use function file_exists;
use function file_put_contents;
use function sys_get_temp_dir;
use function unlink;

/**
 * @group DDC-1698
 */
class AutoloaderTest extends TestCase
{
    public static function dataResolveFile()
    {
        return [
            ['/tmp', 'MyProxy', 'MyProxy\RealClass', '/tmp' . DIRECTORY_SEPARATOR . 'RealClass.php'],
            ['/tmp', 'MyProxy', 'MyProxy\__CG__\RealClass', '/tmp' . DIRECTORY_SEPARATOR . '__CG__RealClass.php'],
            ['/tmp', 'MyProxy\Subdir', 'MyProxy\Subdir\__CG__\RealClass', '/tmp' . DIRECTORY_SEPARATOR . '__CG__RealClass.php'],
            ['/tmp', 'MyProxy', 'MyProxy\__CG__\Other\RealClass', '/tmp' . DIRECTORY_SEPARATOR . '__CG__OtherRealClass.php'],
        ];
    }

    /**
     * @dataProvider dataResolveFile
     */
    public function testResolveFile($proxyDir, $proxyNamespace, $className, $expectedProxyFile)
    {
        $actualProxyFile = Autoloader::resolveFile($proxyDir, $proxyNamespace, $className);
        self::assertEquals($expectedProxyFile, $actualProxyFile);
    }

    public function testAutoload()
    {
        if (file_exists(sys_get_temp_dir() . '/AutoloaderTestClass.php')) {
            unlink(sys_get_temp_dir() . '/AutoloaderTestClass.php');
        }

        $autoloader = Autoloader::register(sys_get_temp_dir(), 'ProxyAutoloaderTest', static function ($proxyDir, $proxyNamespace, $className) {
            file_put_contents(sys_get_temp_dir() . '/AutoloaderTestClass.php', '<?php namespace ProxyAutoloaderTest; class AutoloaderTestClass {} ');
        });

        self::assertTrue(class_exists('ProxyAutoloaderTest\AutoloaderTestClass', true));
        unlink(sys_get_temp_dir() . '/AutoloaderTestClass.php');
    }

    public function testRegisterWithInvalidCallback()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid \$notFoundCallback given: must be a callable, "stdClass" given');

        Autoloader::register('', '', new stdClass());
    }
}
