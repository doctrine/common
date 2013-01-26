<?php

use Doctrine\Common\Reflection\StaticReflectionParser;
use Doctrine\Common\Reflection\ClassFinderInterface;

class PR246Test extends \PHPUnit_Framework_TestCase
{
    public function testUndefinedVariable()
    {
        $parser = new StaticReflectionParser(__CLASS__, new PR246FindFile(), true);
        $parser->getDocComment();
    }
}

class PR246FindFile implements ClassFinderInterface
{
    /**
     * {@inheritdoc}
     */
    public function findFile($class)
    {
        return __FILE__;
    }
}
