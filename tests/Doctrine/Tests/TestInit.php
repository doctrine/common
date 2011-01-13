<?php
/*
 * This file bootstraps the test environment.
 */
namespace Doctrine\Tests;

error_reporting(E_ALL | E_STRICT);


/**
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Runner/Version.php';

if (version_compare(\PHPUnit_Runner_Version::id(), '3.5.0', '>=')) {
    require_once 'PHPUnit/Autoload.php'; // >= PHPUnit 3.5.0
} else {
    require_once 'PHPUnit/Framework.php'; // < PHPUnit 3.5.0
}

require_once 'PHPUnit/TextUI/TestRunner.php';
require_once __DIR__ . '/../../../lib/Doctrine/Common/ClassLoader.php';

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
$classLoader->register();

set_include_path(
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib'
    . PATH_SEPARATOR .
    get_include_path()
);
