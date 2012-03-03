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

/**
 * File cache reader for annotations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class FileCacheReader implements Reader
{
    /**
     * @var Reader
     */
    private $reader;
    private $dir;
    private $debug;
    private $loadedAnnotations = array();

    public function __construct(Reader $reader, $cacheDir, $debug = false)
    {
        $this->reader = $reader;
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777, true)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $cacheDir));
        }
        if (!is_writable($cacheDir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable. Both, the webserver and the console user need access. You can manage access rights for multiple users with "chmod +a". If your system does not support this, check out the acl package.', $cacheDir));
        }

        $this->dir   = rtrim($cacheDir, '\\/');
        $this->debug = (Boolean) $debug;
    }

    /**
     * Gets class annotations.
     *
     * @param ReflectionClass $class The ReflectionClass of the class from which
     *                               the class annotations should be read.
     * @return array The list of annotations.
     */
    public function getClassAnnotations(\ReflectionClass $class)
    {
        $getter = function(Reader $reader) use ($class) {
            return $reader->getClassAnnotations($class);
        };

        return $this->getAnnotations($getter, $class, $class->getName());
    }

    /**
     * Gets a property annotation.
     *
     * @param ReflectionProperty $property
     *
     * @param \ReflectionProperty $property
     * @return array The list of annotations.
     */
    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $key = $class->getName().'$'.$property->getName();

        $getter = function(Reader $reader) use ($property) {
            return $reader->getPropertyAnnotations($property);
        };

        return $this->getAnnotations($getter, $class, $key);
    }

    /**
     * Gets method annotations.
     *
     * @param ReflectionMethod $method
     * @return array The list of annotations.
     */
    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        $reader = $this->reader;
        $class = $method->getDeclaringClass();
        $key = $class->getName().'#'.$method->getName();

        $getter = function(Reader $reader) use ($method) {
            return $reader->getMethodAnnotations($method);
        };

        return $this->getAnnotations($getter, $class, $key);
    }

    /**
     * Gets a class annotation.
     *
     * @param ReflectionClass $class The ReflectionClass of the class from which
     *                               the class annotations should be read.
     * @param string $annotationName The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getClassAnnotation(\ReflectionClass $class, $annotationName)
    {
        return $this->filterAnnotationsByName($this->getClassAnnotations($class), $annotationName);
    }

    /**
     * Gets a method annotation.
     *
     * @param ReflectionMethod $method
     * @param string $annotationName The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        return $this->filterAnnotationsByName($this->getMethodAnnotations($method), $annotationName);
    }

    /**
     * Gets a property annotation.
     *
     * @param ReflectionProperty $property
     * @param string $annotationName The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
        return $this->filterAnnotationsByName($this->getPropertyAnnotations($property), $annotationName);
    }

    public function clearLoadedAnnotations()
    {
        $this->loadedAnnotations = array();
    }

    /**
     * Return the annotation of the given type.
     *
     * @param type $annotations The list of annotations.
     * @param type $name        The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
     */
    private function filterAnnotationsByName($annotations, $name)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $name) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Returns the annotations and handle the cache.
     *
     * @param \Closure         $getter A closure returning the target annotations.
     * @param \ReflectionClass $class  The target related class.
     * @param string           $key    The target UID.
     * @return array The target annotations.
     */
    private function getAnnotations(\Closure $getter, \ReflectionClass $class, $key)
    {
        if (isset($this->loadedAnnotations[$key])) {
            return $this->loadedAnnotations[$key];
        }

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            unlink($path);
        }

        if (!file_exists($path)) {
            $annot = $getter($this->reader);
            file_put_contents($path, '<?php return unserialize('.var_export(serialize($annot), true).');');
            return $this->loadedAnnotations[$key] = $annot;
        }

        return $this->loadedAnnotations[$key] = include $path;
    }
}