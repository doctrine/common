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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Karoly Negyesi <karoly@negyesi.net>
 */
class Psr0AnnotationReader extends PhpParser {

    /**
     * The name of the class.
     *
     * @var string
     */
    protected $className;

    /**
     * Base include path for class files.
     *
     * @var string
     */
    protected $includePath;

    /**
     * The PHP namespace this class is in.
     *
     * @var string
     */
    protected $ns;

    /**
     * Annotations Parser
     *
     * @var \Doctrine\Common\Annotations\DocParser
     */
    protected $parser;

    /**
     * The annotations on the class.
     *
     * @var array
     */
    protected $classAnnotations;

    /**
     * Annotations on methods.
     *
     * @var array
     */
    protected $methodAnnotations;

    /**
     * Annotations on properties.
     *
     * @var array
     */
    protected $propertyAnnotations;

    /**
     * The name of the class this class extends, if any.
     *
     * @var string
     */
    protected $parentClassName = '';

    /**
     * The parent annotation reader.
     *
     * @var \Doctrine\Common\Annotations\Psr0AnnotationReader
     */
    protected $parentAnnotationReader;

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    protected static $globalIgnoredNames = array(
        'access'=> true, 'author'=> true, 'copyright'=> true, 'deprecated'=> true,
        'example'=> true, 'ignore'=> true, 'internal'=> true, 'link'=> true, 'see'=> true,
        'since'=> true, 'tutorial'=> true, 'version'=> true, 'package'=> true,
        'subpackage'=> true, 'name'=> true, 'global'=> true, 'param'=> true,
        'return'=> true, 'staticvar'=> true, 'category'=> true, 'staticVar'=> true,
        'static'=> true, 'var'=> true, 'throws'=> true, 'inheritdoc'=> true,
        'inheritDoc'=> true, 'license'=> true, 'todo'=> true,
        'deprec'=> true, 'property' => true, 'method' => true,
        'abstract'=> true, 'exception'=> true, 'magic' => true, 'api' => true,
        'final'=> true, 'filesource'=> true, 'throw' => true, 'uses' => true,
        'usedby'=> true, 'private' => true, 'Annotation' => true, 'override' => true,
        'codeCoverageIgnore' => true, 'codeCoverageIgnoreStart' => true, 'codeCoverageIgnoreEnd' => true,
        'Required' => true, 'Attribute' => true, 'Attributes' => true,
        'Target' => true, 'SuppressWarnings' => true,
    );

    /**
     * TRUE if the use statements has been passed to the parser already.
     */
    protected $passedUse = FALSE;

    /**
     * Parses a class.
     *
     * @param string $includePath Base include path for class files.
     * @param string $class The full class name.
     */
    public function __construct($includePath, $className)
    {
        $className = ltrim($className, '\\');
        $this->includePath = $includePath;
        $fileName  = $includePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        if (!file_exists($fileName)) {
            return array();
        }
        $lastNsPos = strripos($className, '\\');
        $this->ns = substr($className, 0, $lastNsPos);
        $this->className = substr($className, $lastNsPos + 1);
        $this->parser = new DocParser;
        $this->parser->setIgnoredAnnotationNames(self::$globalIgnoredNames);
        $this->tokens = token_get_all(file_get_contents($fileName));
        $this->numTokens = count($this->tokens);
        $this->pointer = 0;
        $annotations = array();
        $statements = array();
        while ($token = $this->next(FALSE)) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_USE:
                        $statements = array_merge($statements, $this->parseUseStatement());
                        break;
                    case T_DOC_COMMENT:
                        $this->passUseStatements($statements);
                        $annotations = $this->parser->parse($token[1]);
                        break;
                    case T_CLASS:
                        $this->classAnnotations = $annotations;
                        $annotations = array();
                        break;
                    case T_FUNCTION:
                        $token = $this->next();
                        $methodName = $token[1];
                        $this->methodAnnotations[$methodName] = $annotations;
                        $annotations = array();
                        break;
                    case T_PRIVATE:
                    case T_PROTECTED:
                    case T_PUBLIC:
                        $token = $this->next();
                        // Slice off the $ sign from the T_VARIABLE;
                        $propertyName = substr($token[1], 1);
                        $this->propertyAnnotations[$propertyName] = $annotations;
                        $annotations = array();
                        break;
                    case T_EXTENDS:
                        $token = $this->next();
                        $this->parentClassName = $token[1];
                        if ($this->parentClassName[0] !== '\\') {
                          $this->parentClassName = $this->ns . '\\' . $this->parentClass;
                        }
                        break;
                }
            }
        }
        // Drop what we can to save memory.
        unset($this->tokens, $this->parser);
    }

    /**
     * Add a new annotation to the globally ignored annotation names with regard to exception handling.
     *
     * @param string $name
     */
    static public function addGlobalIgnoredName($name)
    {
        self::$globalIgnoredNames[$name] = true;
    }

    /**
     * Gets a specific annotation.
     */
    protected function getAnnotation($annotations, $annotationName)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }
    }

    /**
     * Gets all class annotations.
     *
     * @return array
     */
    public function getClassAnnotations()
    {
      return $this->classAnnotations;
    }

    /**
     * Gets a class annotation.
     *
     * @param string $annotationName The name of the annotation.
     * @return mixed The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getClassAnnotation($annotationName)
    {
        return $this->getAnnotation($this->classAnnotations, $annotationName);
    }

    /**
     * Gets the annotations applied to a property.
     *
     * @param $propertyName The name of the property from which the annotations should be read.
     * @return array An array of Annotations.
     */
    public function getPropertyAnnotations($propertyName)
    {
        if (isset($this->propertyAnnotations[$propertyName])) {
          return $this->propertyAnnotations[$propertyName];
        }
        if ($parentAnnotationReader = $this->getParentAnnotationReader()) {
          return $parentAnnotationReader->getPropertyAnnotations($propertyName);
        }
        return array();
    }

    /**
     * Gets a property annotation.
     *
     * @param $propertyName The name of the property from which the annotations should be read.
     * @param string $annotationName The name of the annotation.
     * @return mixed The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getPropertyAnnotation($propertyName, $annotationName)
    {
        if (isset($this->propertyAnnotations[$propertyName])) {
          return $this->getAnnotation($this->propertyAnnotations[$propertyName], $annotationName);
        }
        if ($parentAnnotationReader = $this->getParentAnnotationReader()) {
          return $parentAnnotationReader->getPropertyAnnotation($propertyName, $annotationName);
        }
    }

    /**
     * Gets the annotations applied to a method.
     *
     * @param $methodName The name of the method from which the annotations should be read.
     * @return array An array of Annotations.
     */
    public function getMethodAnnotations($methodName)
    {
        if (isset($this->methodAnnotations[$methodName])) {
          return $this->methodAnnotations[$methodName];
        }
        if ($parentAnnotationReader = $this->getParentAnnotationReader()) {
          return $parentAnnotationReader->getMethodAnnotations($methodName);
        }
        return array();
    }

    /**
     * Gets a method annotation.
     *
     * @param $methodName The name of the method from which the annotations should be read.
     * @param string $annotationName The name of the annotation.
     * @return mixed The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getMethodAnnotation($methodName, $annotationName)
    {
        if (isset($this->methodAnnotations[$methodName])) {
          return $this->getAnnotation($this->methodAnnotations[$propertyName], $annotationName);
        }

        if ($parentAnnotationReader = $this->getParentAnnotationReader()) {
          return $parentAnnotationReader->methodAnnotations($methodName);
        }
    }

    /**
     * Returns a reader for the parent class, if any
     *
     * @return mixed Either an instance of the same class as the current
     * reader or null if the
     *    class doesn't extend anything.
     */
    protected function getParentAnnotationReader()
    {
        if (empty($this->parentAnnotationReader)) {
            $class = get_class($this);
            $this->parentAnnotationReader = new $class($this->includePath, $this->parentClass);
        }
        return $this->parentAnnotationReader;
    }

    protected function passUseStatements($imports)
    {
        if (!$this->passedUse) {
            $this->passedUse = TRUE;
            $imports['__NAMESPACE__'] = $this->ns;
            $this->parser->setImports($imports);
        }
    }
}
