<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\DocLexer;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\AnnotationReader;

class PerformanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group performance
     */
    public function testCachedReadPerformanceWithInMemory()
    {
        $reader = new CachedReader(new AnnotationReader(), new ArrayCache());
        $method = $this->getMethod();

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $reader->getMethodAnnotations($method);
        }
        $time = microtime(true) - $time;

        $this->printResults('cached reader (in-memory)', $time, $c);
        
    }
    
    /**
     * @group performance
     */
    public function testCachedReadPerformanceWithInMemoryWithMarkedAnnotation()
    {
        $reader = new CachedReader(new AnnotationReader(), new ArrayCache());
        $method = $this->getMethodWithMarkedAnnotation();

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $reader->getMethodAnnotations($method);
        }
        $time = microtime(true) - $time;

        $this->printResults('cached reader with markers (in-memory)', $time, $c);
    }

    /**
     * @group performance
     */
    public function testCachedReadPerformanceWithFileCache()
    {
        $method = $this->getMethod();

        // prime cache
        $reader = new FileCacheReader(new AnnotationReader(), sys_get_temp_dir());
        $reader->getMethodAnnotations($method);

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $reader = new FileCacheReader(new AnnotationReader(), sys_get_temp_dir());
            $reader->getMethodAnnotations($method);
            clearstatcache();
        }
        $time = microtime(true) - $time;

        $this->printResults('cached reader (file)', $time, $c);
    }
    /**
     * @group performance
     */
    public function testCachedReadPerformanceWithFileCacheWithMarkedAnnotation()
    {
        $method = $this->getMethodWithMarkedAnnotation();

        // prime cache
        $reader = new FileCacheReader(new AnnotationReader(), sys_get_temp_dir());
        $reader->getMethodAnnotations($method);

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $reader = new FileCacheReader(new AnnotationReader(), sys_get_temp_dir());
            $reader->getMethodAnnotations($method);
            clearstatcache();
        }
        $time = microtime(true) - $time;

        $this->printResults('cached reader with markers (file)', $time, $c);
    }
    
    
    

    /**
     * @group performance
     */
    public function testReadPerformance()
    {
        $reader = new AnnotationReader();
        $method = $this->getMethod();

        $time = microtime(true);
        for ($i=0,$c=150; $i<$c; $i++) {
            $reader = new AnnotationReader();
            $reader->getMethodAnnotations($method);
        }
        $time = microtime(true) - $time;

        $this->printResults('reader', $time, $c);
    }
    
    
    /**
     * @group performance
     */
    public function testReadPerformanceWithMarkedAnnotation()
    {
        $reader = new AnnotationReader();
        $method = $this->getMethodWithMarkedAnnotation();

        $time = microtime(true);
        for ($i=0,$c=150; $i<$c; $i++) {
            $reader = new AnnotationReader();
            $items  = $reader->getMethodAnnotations($method);
        }
        $time = microtime(true) - $time;

        $this->printResults('reader with markers', $time, $c);
    }
    
    
    

    /**
     * @group performance
     */
    public function testDocParsePerformance()
    {
        $imports = array(
            'ignorephpdoc'     => 'Annotations\Annotation\IgnorePhpDoc',
            'ignoreannotation' => 'Annotations\Annotation\IgnoreAnnotation',
            'route'            => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route',
            'template'         => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template',
            '__NAMESPACE__'    => 'Doctrine\Tests\Common\Annotations\Fixtures',
        );
        $ignored = array(
            'access' => true, 'author' => true, 'copyright' => true, 'deprecated' => true,
            'example' => true, 'ignore' => true, 'internal' => true, 'link' => true, 'see' => true,
            'since' => true, 'tutorial' => true, 'version' => true, 'package' => true,
            'subpackage' => true, 'name' => true, 'global' => true, 'param' => true,
            'return' => true, 'staticvar' => true, 'category' => true, 'staticVar' => true,
            'static' => true, 'var' => true, 'throws' => true, 'inheritdoc' => true,
            'inheritDoc' => true, 'license' => true, 'todo' => true, 'deprecated' => true,
            'deprec' => true, 'author' => true, 'property' => true, 'method' => true,
            'abstract' => true, 'exception' => true, 'magic' => true, 'api' => true,
            'final' => true, 'filesource' => true, 'throw' => true, 'uses' => true,
            'usedby' => true, 'private' => true
        );

        $parser = new DocParser();
        $method = $this->getMethod();
        $methodComment = $method->getDocComment();
        $classComment = $method->getDeclaringClass()->getDocComment();

        $time = microtime(true);
        for ($i=0,$c=200; $i<$c; $i++) {
            $parser = new DocParser();
            $parser->setImports($imports);
            $parser->setIgnoredAnnotationNames($ignored);

            $parser->parse($methodComment);
            $parser->parse($classComment);
        }
        $time = microtime(true) - $time;

        $this->printResults('doc-parser', $time, $c);
    }
    
    

    /**
     * @group performance
     */
    public function testDocLexerPerformance()
    {
        $lexer = new DocLexer();
        $method = $this->getMethod();
        $methodComment = $method->getDocComment();
        $classComment = $method->getDeclaringClass()->getDocComment();

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $lexer = new DocLexer();
            $lexer->setInput($methodComment);
            $lexer->setInput($classComment);
        }
        $time = microtime(true) - $time;

        $this->printResults('doc-lexer', $time, $c);
    }
    
    

    


    private function getMethod()
    {
        return new \ReflectionMethod('Doctrine\Tests\Common\Annotations\Fixtures\Controller', 'helloAction');
    }
    
    private function getMethodWithMarkedAnnotation()
    {
        return new \ReflectionMethod('Doctrine\Tests\Common\Annotations\Fixtures\Controller', 'helloActionWithMarkedAnnotation');
    }

    private function printResults($test, $time, $iterations)
    {
        if (0 == $iterations) {
            throw new \InvalidArgumentException('$iterations cannot be zero.');
        }

        $title = $test." results:\n";
        $iterationsText = sprintf("Iterations:         %d\n", $iterations);
        $totalTime      = sprintf("Total Time:         %.3f s\n", $time);
        $iterationTime  = sprintf("Time per iteration: %.3f ms\n", $time/$iterations * 1000);

        $max = max(strlen($title), strlen($iterationTime)) - 1;

        echo "\n".str_repeat('-', $max)."\n";
        echo $title;
        echo str_repeat('=', $max)."\n";
        echo $iterationsText;
        echo $totalTime;
        echo $iterationTime;
        echo str_repeat('-', $max)."\n";
    }
}