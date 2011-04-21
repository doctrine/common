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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

use Closure,
    ReflectionClass,
    ReflectionMethod,
    ReflectionProperty,
    Doctrine\Common\Cache\Cache,
    Doctrine\Common\Annotations\Import;

/**
 * A reader for docblock annotations.
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AnnotationReader
{
    /**
     * Cache salt
     *
     * @var string
     * @static
     */
    private static $CACHE_SALT = '@[Annot]';

    /**
     * Annotations Parser
     *
     * @var Doctrine\Common\Annotations\Parser
     */
    private $parser;

    /**
     * Annotations Parser used to collect parsing metadata
     *
     * @var Doctrine\Common\Annotations\Parser
     */
    private $preParser;

    /**
     * Cache mechanism to store processed Annotations
     *
     * @var Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * Global map for imports.
     *
     * @var array
     */
    private $globalImports = array(
        'Doctrine\Common\Annotations\Import' => null,
        'Doctrine\Common\Annotations\IgnoreAnnotation' => null,
        'Doctrine\Common\Annotations\IgnorePhpDoc' => null,
    );

    /**
     * In-memory cache mechanism to store imported annotations per class.
     *
     * @var array
     */
    private $imports = array();

    /**
     * In-memory cache mechanism to store ignored annotations per class.
     *
     * @var array
     */
    private $ignoredAnnotationNames = array();

    /**
     * Constructor. Initializes a new AnnotationReader that uses the given Cache provider.
     *
     * @param Cache $cache The cache provider to use. If none is provided, ArrayCache is used.
     * @param Parser $parser The parser to use. If none is provided, the default parser is used.
     */
    public function __construct(Cache $cache = null, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser;
        $this->cache = $cache ?: new \Doctrine\Common\Cache\ArrayCache;

        $this->preParser = new Parser();
        $this->preParser->setImports($this->globalImports);
        $this->preParser->setIndexByClass(false);
        $this->preParser->setIgnoreNotImportedAnnotations(true);
    }

    /**
     * Sets the default namespace that the AnnotationReader should assume for annotations
     * with not fully qualified names.
     *
     * @param string $defaultNamespace
     * @deprecated use the @import annotation, or addGlobalImport instead
     */
    public function setDefaultAnnotationNamespace($defaultNamespace)
    {
        $this->addGlobalImport($defaultNamespace.'*');
    }

    /**
     * Sets the global imports for this reader.
     *
     * @param array $imports
     */
    public function setGlobalImports(array $imports)
    {
        $this->globalImports = $imports;
    }

    /**
     * Adds a namespace to the global import map.
     *
     * Allowed namespace values are either
     * - an entire namespace "MyNamespace\Annotation\*"
     * - or a single annotation class "MyNamespace\Annotation\Foo"
     *
     * NOTE: Generally, it is recommended that you add imports on the
     *       class-level using the @import annotation.
     *
     * @param string $namespace
     * @param string $alias
     */
    public function addGlobalImport($namespace, $alias = null)
    {
        $this->globalImports[$namespace] = $alias;
    }

    /**
     * Returns an array of global imports that are used by this reader
     *
     * @return array
     */
    public function getGlobalImports()
    {
        return $this->globalImports;
    }

    /**
     * Sets the custom function to use for creating new annotations on the
     * underlying parser.
     *
     * The function is supplied two arguments. The first argument is the name
     * of the annotation and the second argument an array of values for this
     * annotation. The function is assumed to return an object or NULL.
     * Whenever the function returns NULL for an annotation, the implementation falls
     * back to the default annotation creation process of the underlying parser.
     *
     * @param Closure $func
     */
    public function setAnnotationCreationFunction(Closure $func)
    {
        $this->parser->setAnnotationCreationFunction($func);
    }

    /**
     * Sets an alias for an annotation namespace.
     *
     * @param string $namespace
     * @param string $alias
     * @deprecated use the @import annotation, or addGlobalImport instead
     */
    public function setAnnotationNamespaceAlias($namespace, $alias)
    {
        $this->addGlobalImport($namespace.'*', $alias);
    }

    /**
     * Sets a flag whether to auto-load annotation classes or not.
     *
     * NOTE: It is recommended to turn auto-loading on if your auto-loader
     *       supports silent failing. For this reason, setting this to TRUE
     *       renders the parser incompatible with {@link ClassLoader}.
     *
     * @param boolean $bool Boolean flag.
     */
    public function setAutoloadAnnotations($bool)
    {
        $this->parser->setAutoloadAnnotations($bool);
    }

    /**
     * Gets a flag whether to try to autoload annotation classes.
     *
     * @see setAutoloadAnnotations
     * @return boolean
     */
    public function getAutoloadAnnotations()
    {
        return $this->parser->getAutoloadAnnotations();
    }

    /**
     * Whether annotations which have not been explicitly imported should be
     * ignored. Turning this off, will throw an exception if an annotation that
     * is used inside the class is not imported.
     *
     * @param boolean $bool
     */
    public function setIgnoreNotImportedAnnotations($bool)
    {
        $this->parser->setIgnoreNotImportedAnnotations($bool);
    }

    /**
     * Whether annotations should be indexed by class name (the default).
     *
     * NOTE: If you leave this turned on, then you cannot have annotations with
     *       the same class on top-level.
     *
     * @param boolean $bool
     */
    public function setIndexByClass($bool)
    {
        $this->parser->setIndexByClass($bool);
    }

    /**
     * Gets the annotations applied to a class.
     *
     * @param string|ReflectionClass $class The name or ReflectionClass of the class from which
     * the class annotations should be read.
     * @return array An array of Annotations.
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $cacheKey = $class->getName() . self::$CACHE_SALT;

        // Attempt to grab data from cache
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            return $data;
        }

        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $annotations = $this->parser->parse($class->getDocComment(), 'class ' . $class->getName());
        $this->cache->save($cacheKey, $annotations, null);

        return $annotations;
    }

    /**
     * Gets a class annotation.
     *
     * @param ReflectionClass $class The ReflectionClass of the class from which
     * the class annotations should be read.
     * @param string $annotation The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getClassAnnotation(ReflectionClass $class, $annotation)
    {
        $annotations = $this->getClassAnnotations($class);

        return isset($annotations[$annotation]) ? $annotations[$annotation] : null;
    }

    /**
     * Gets the annotations applied to a property.
     *
     * @param string|ReflectionProperty $property The name or ReflectionProperty of the property
     * from which the annotations should be read.
     * @return array An array of Annotations.
     */
    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $cacheKey = $property->getDeclaringClass()->getName() . '$' . $property->getName() . self::$CACHE_SALT;

        // Attempt to grab data from cache
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            return $data;
        }

        $context = 'property ' . $property->getDeclaringClass()->getName() . "::\$" . $property->getName();
        $class = $property->getDeclaringClass();
        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $annotations = $this->parser->parse($property->getDocComment(), $context);
        $this->cache->save($cacheKey, $annotations, null);

        return $annotations;
    }

    /**
     * Gets a property annotation.
     *
     * @param ReflectionProperty $property
     * @param string $annotation The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $annotation)
    {
        $annotations = $this->getPropertyAnnotations($property);

        return isset($annotations[$annotation]) ? $annotations[$annotation] : null;
    }

    /**
     * Gets the annotations applied to a method.
     *
     * @param ReflectionMethod $property The name or ReflectionMethod of the method from which
     * the annotations should be read.
     * @return array An array of Annotations.
     */
    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $cacheKey = $method->getDeclaringClass()->getName() . '#' . $method->getName() . self::$CACHE_SALT;

        // Attempt to grab data from cache
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            return $data;
        }

        $context = 'method ' . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()';
        $class = $method->getDeclaringClass();
        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $annotations = $this->parser->parse($method->getDocComment(), $context);
        $this->cache->save($cacheKey, $annotations, null);

        return $annotations;
    }

    /**
     * Gets a method annotation.
     *
     * @param ReflectionMethod $method
     * @param string $annotation The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getMethodAnnotation(ReflectionMethod $method, $annotation)
    {
        $annotations = $this->getMethodAnnotations($method);

        return isset($annotations[$annotation]) ? $annotations[$annotation] : null;
    }

    /**
     * Returns the imports applicable for a given class.
     *
     * @param ReflectionClass $class
     * @return array
     */
    public function getImports(ReflectionClass $class)
    {
        if (isset($this->imports[$name = $class->getName()])) {
            return $this->imports[$name];
        }
        $this->collectParsingMetadata($class);

        return $this->imports[$name];
    }

    /**
     * Returns the ignored annotations for the given class.
     *
     * @param ReflectionClass $class
     * @return array
     */
    public function getIgnoredAnnotationNames(ReflectionClass $class)
    {
        if (isset($this->ignoredAnnotationNames[$name = $class->getName()])) {
            return $this->ignoredAnnotationNames[$name];
        }
        $this->collectParsingMetadata($class);

        return $this->ignoredAnnotationNames[$name];
    }

    /**
     * Collects parsing metadata for a given class
     *
     * @param ReflectionClass $class
     */
    private function collectParsingMetadata(ReflectionClass $class)
    {
        $imports = $this->globalImports;
        $ignoredAnnotationNames = array();

        $annotations = $this->preParser->parse($class->getDocComment());
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Import) {
                $imports[$annotation->getNamespace()] = $annotation->getAlias();
                continue;
            } else if ($annotation instanceof IgnoreAnnotationsInterface) {
                $ignoredAnnotationNames = array_merge($ignoredAnnotationNames, $annotation->getNames());
            }
        }

        $name = $class->getName();
        $this->imports[$name] = $imports;
        $this->ignoredAnnotationNames[$name] = array_unique($ignoredAnnotationNames);
    }
}
