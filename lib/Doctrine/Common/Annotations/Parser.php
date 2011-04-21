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

use Closure, Doctrine\Common\ClassLoader;

/**
 * A simple parser for docblock annotations.
 *
 * This Parser can be subclassed to customize certain aspects of the annotation
 * parsing and/or creation process. Note though that currently no special care
 * is taken to maintain full backwards compatibility for subclasses. Implementation
 * details of the default Parser can change without explicit notice.
 *
 * @since 2.0
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Parser
{
    /**
     * Some common tags that are stripped prior to parsing in order to reduce parsing overhead.
     *
     * @var array
     */
    private static $strippedTags = array(
        "{@internal", "{@inheritdoc", "{@link"
    );

    /**
     * The lexer.
     *
     * @var Doctrine\Common\Annotations\Lexer
     */
    private $lexer;

    /**
     * Flag to control if the current annotation is nested or not.
     *
     * @var boolean
     */
    protected $isNestedAnnotation = false;

    /**
     * Hashmap containing all use-statements that are to be used when parsing
     * the given doc block.
     *
     * @var array
     */
    private $imports = array();

    /**
     * This hashmap is used internally to cache results of class_exists()
     * look-ups.
     *
     * @var array
     */
    private $classExists = array();

    /**
     * Whether to index annotations by their class.
     *
     * If set to true, duplicate annotations will override each other.
     *
     * @var boolean
     */
    private $indexByClass = true;

    /**
     * Whether annotations that have not been imported should be ignored.
     *
     * @var boolean
     */
    private $ignoreNotImportedAnnotations = true;

    /**
     * A list with annotations that are to be ignored during the parsing process.
     *
     * The names must be the raw names as used in the class, not the fully qualified
     * class names.
     *
     * @var array
     */
    private $ignoredAnnotationNames = array();

    /**
     * @var string
     */
    private $context = '';

    /**
     * @var Closure The custom function used to create new annotations, if any.
     */
    private $annotationCreationFunction;

    /**
     * @var boolean
     */
    private $autoloadAnnotations = false;

    /**
     * Constructs a new AnnotationParser.
     */
    public function __construct(Lexer $lexer = null)
    {
        $this->lexer = $lexer ?: new Lexer;
    }

    /**
     * Gets the lexer used by this parser.
     *
     * @return Lexer The lexer.
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    /**
     * Sets the annotation names that are ignored during the parsing process.
     *
     * The names are supposed to be the raw names as used in the class, not the
     * fully qualified class names.
     *
     * @param array $names
     */
    public function setIgnoredAnnotationNames(array $names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    /**
     * Sets a flag whether to auto-load annotation classes or not.
     *
     * NOTE: It is recommend to turn auto-loading on if your auto-loader support
     *       silent failing. For this reason, setting this to TRUE renders the
     *       parser incompatible with {@link ClassLoader}.
     *
     * @param boolean $bool Boolean flag.
     */
    public function setAutoloadAnnotations($bool)
    {
        $this->autoloadAnnotations = $bool;
    }

    /**
     * Sets the default namespace that is assumed for an annotation that does not
     * define a namespace prefix.
     *
     * @param string $defaultNamespace
     * @deprecated use setImports instead
     */
    public function setDefaultAnnotationNamespace($defaultNamespace)
    {
        $this->imports[$defaultNamespace.'*'] = null;
    }

    /**
     * Sets an alias for an annotation namespace.
     *
     * @param string $namespace
     * @param string $alias
     * @deprecated use setImports instead
     */
    public function setAnnotationNamespaceAlias($namespace, $alias)
    {
        $this->imports[$namespace.'*'] = $alias;
    }

    /**
     * Sets the custom function to use for creating new annotations.
     *
     * The function is supplied two arguments. The first argument is the name
     * of the annotation and the second argument an array of values for this
     * annotation. The function is assumed to return an object or NULL.
     * Whenever the function returns NULL for an annotation, the parser falls
     * back to the default annotation creation process.
     *
     * Whenever the function returns NULL for an annotation, the implementation falls
     * back to the default annotation creation process.
     *
     * @param Closure $func
     */
    public function setAnnotationCreationFunction(Closure $func)
    {
        $this->annotationCreationFunction = $func;
    }

    /**
     * Gets a flag whether to try to autoload annotation classes.
     *
     * @see setAutoloadAnnotations
     * @return boolean
     */
    public function getAutoloadAnnotations()
    {
        return $this->autoloadAnnotations;
    }

    public function setImports(array $imports)
    {
        $this->imports = $imports;
    }

    public function setIndexByClass($bool)
    {
        $this->indexByClass = (Boolean) $bool;
    }

    public function setIgnoreNotImportedAnnotations($bool)
    {
        $this->ignoreNotImportedAnnotations = (Boolean) $bool;
    }

    /**
     * Parses the given docblock string for annotations.
     *
     * @param string $docBlockString The docblock string to parse.
     * @param string $context The parsing context.
     * @return array Array of annotations. If no annotations are found, an empty array is returned.
     */
    public function parse($docBlockString, $context='')
    {
        $this->context = $context;

        // Strip out some known inline tags.
        $input = str_replace(self::$strippedTags, '', $docBlockString);

        // Cut of the beginning of the input until the first '@'.
        $input = substr($input, strpos($input, '@'));

        $this->lexer->reset();
        $this->lexer->setInput(trim($input, '* /'));
        $this->lexer->moveNext();

        if ($this->lexer->isNextToken(Lexer::T_AT)) {
            return $this->Annotations();
        }

        return array();
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param int Token type.
     * @return bool True if tokens match; false otherwise.
     */
    public function match($token)
    {
        if ( ! ($this->lexer->lookahead['type'] === $token)) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }
        $this->lexer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string $expected Expected string.
     * @param array $token Optional token.
     * @throws AnnotationException
     */
    private function syntaxError($expected, $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $message =  "Expected {$expected}, got ";

        if ($this->lexer->lookahead === null) {
            $message .= 'end of string';
        } else {
            $message .= "'{$token['value']}' at position {$token['position']}";
        }

        if (strlen($this->context)) {
            $message .= ' in ' . $this->context;
        }

        $message .= '.';

        throw AnnotationException::syntaxError($message);
    }

    /**
     * This will prevent going through the auto-loader on each occurence of the
     * annotation.
     *
     * @param string $fqcn
     * @return boolean
     */
    private function classExists($fqcn)
    {
        if (isset($this->classExists[$fqcn])) {
            return $this->classExists[$fqcn];
        }

        return $this->classExists[$fqcn] = class_exists($fqcn, $this->autoloadAnnotations);
    }

    /**
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     *
     * @return array
     */
    public function Annotations()
    {
        $this->isNestedAnnotation = false;

        $annotations = array();
        $annot = $this->Annotation();

        if ($annot !== false) {
            if ($this->indexByClass) {
                $annotations[get_class($annot)] = $annot;
            } else {
                $annotations[] = $annot;
            }
            $this->lexer->skipUntil(Lexer::T_AT);
        }

        while ($this->lexer->lookahead !== null && $this->lexer->isNextToken(Lexer::T_AT)) {
            $this->isNestedAnnotation = false;
            $annot = $this->Annotation();

            if ($annot !== false) {
                if ($this->indexByClass) {
                    $annotations[get_class($annot)] = $annot;
                } else {
                    $annotations[] = $annot;
                }
                $this->lexer->skipUntil(Lexer::T_AT);
            }
        }

        return $annotations;
    }

    /**
     * Annotation     ::= "@" AnnotationName ["(" [Values] ")"]
     * AnnotationName ::= QualifiedName | SimpleName | AliasedName
     * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
     * AliasedName    ::= Alias ":" SimpleName
     * NameSpacePart  ::= identifier
     * SimpleName     ::= identifier
     * Alias          ::= identifier
     *
     * @return mixed False if it is not a valid annotation.
     */
    public function Annotation()
    {
        $values = array();

        $this->match(Lexer::T_AT);

        // check if we have an annotation
        if (!$this->lexer->isNextToken(Lexer::T_NAMESPACE_SEPARATOR)
            && !$this->lexer->isNextToken(Lexer::T_IDENTIFIER)) {
            if ($this->isNestedAnnotation === false) {
                $this->lexer->skipUntil(Lexer::T_AT);
                return false;
            }

            // this will trigger an exception
            $this->match(Lexer::T_IDENTIFIER);
        } else if ($this->lexer->isNextToken(Lexer::T_IDENTIFIER)) {
            $this->lexer->moveNext();
            $name = $this->lexer->token['value'];
        } else {
            $name = '';
        }

        while ($this->lexer->isNextToken(Lexer::T_NAMESPACE_SEPARATOR)) {
            $this->match(Lexer::T_NAMESPACE_SEPARATOR);
            $this->match(Lexer::T_IDENTIFIER);
            $name .= '\\'.$this->lexer->token['value'];
        }

        // check if name is supposed to be ignored
        if (in_array($name, $this->ignoredAnnotationNames, true)) {
            $this->lexer->skipUntil(Lexer::T_AT);
            return false;
        }

        // only process names which are not fully qualified, yet
        if ((false !== $pos = strpos($name, ':')) || false === strpos($name, '\\')) {
            $alias = null;

            // check if it has an alias
            if (false !== $pos) {
                $alias = substr($name, 0, $pos);
                $name  = substr($name, $pos+1);
            }

            $foundFqcn = array();
            // check if annotation has been imported
            foreach (array_keys($this->imports, $alias, true) as $namespace) {
                // entire sub-namespace has been imported
                if ('*' === substr($namespace, -1)) {
                    $fqcn = substr($namespace, 0, -1).$name;

                    if ($this->classExists($fqcn)) {
                        $foundFqcn[] = $fqcn;
                    }
                }
                // a specific annotation has been imported
                else if ((false !== $pos = strrpos($namespace, '\\'))
                         && strtolower(substr($namespace, $pos+1)) === strtolower($name)) {
                    if (!$this->classExists($namespace)) {
                        throw AnnotationException::semanticalError(sprintf(
                            'The imported annotation class "%s" does not exist.', $namespace
                        ));
                    }

                    $foundFqcn[] = $namespace;
                }
            }

            if (!$foundFqcn) {
                if ($this->ignoreNotImportedAnnotations) {
                    $this->lexer->skipUntil(Lexer::T_AT);
                    return false;
                }

                throw AnnotationException::semanticalError(sprintf('The annotation "@%s" was never imported.', $name));
            }

            if (count($foundFqcn = array_unique($foundFqcn)) > 1) {
                throw AnnotationException::semanticalError(sprintf('The annotation "@%s" was found in several imports: %s', $name, implode(', ', $foundFqcn)));
            }

            $name = reset($foundFqcn);
        } else if (!$this->classExists($name)) {
            throw AnnotationException::semanticalError(sprintf('The annotation "@%s" does not exist, or could not be auto-loaded.', $name));
        }

        // at this point, $name contains the fully qualified class name of the
        // annotation, and it is also guaranteed that this class exists, and
        // that it is loaded

        // Next will be nested
        $this->isNestedAnnotation = true;

        $this->lexer->skipWhitespace();
        if ($this->lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $this->match(Lexer::T_OPEN_PARENTHESIS);

            $this->lexer->skipWhitespace();
            if ( ! $this->lexer->isNextToken(Lexer::T_CLOSE_PARENTHESIS)) {
                $values = $this->Values();
            }

            $this->lexer->skipWhitespace();
            $this->match(Lexer::T_CLOSE_PARENTHESIS);
        }

        if ($this->annotationCreationFunction !== null) {
            $func = $this->annotationCreationFunction;
            $annot = $func($name, $values);
        }

        return isset($annot) ? $annot : $this->newAnnotation($name, $values);
    }

    /**
     * Values ::= Array | Value {"," Value}*
     *
     * @return array
     */
    public function Values()
    {
        $values = array();

        $this->lexer->skipWhitespace();

        // Handle the case of a single array as value, i.e. @Foo({....})
        if ($this->lexer->isNextToken(Lexer::T_OPEN_CURLY_BRACES)) {
            $values['value'] = $this->Value();
            return $values;
        }

        $values[] = $this->Value();

        $this->lexer->skipWhitespace();
        while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $token = $this->lexer->lookahead;
            $value = $this->Value();

            if ( ! is_object($value) && ! is_array($value)) {
                $this->syntaxError('Value', $token);
            }

            $values[] = $value;
            $this->lexer->skipWhitespace();
        }

        foreach ($values as $k => $value) {
            if (is_object($value) && $value instanceof \stdClass) {
                $values[$value->name] = $value->value;
            } else if ( ! isset($values['value'])){
                $values['value'] = $value;
            } else {
                if ( ! is_array($values['value'])) {
                    $values['value'] = array($values['value']);
                }

                $values['value'][] = $value;
            }

            unset($values[$k]);
        }

        return $values;
    }

    /**
     * Value ::= PlainValue | FieldAssignment
     *
     * @return mixed
     */
    public function Value()
    {
        $this->lexer->skipWhitespace();

        $this->lexer->resetPeek();
        while ((null !== $peak = $this->lexer->peek()) && $peak['type'] === Lexer::T_WHITESPACE);

        if ($peak['type'] === Lexer::T_EQUALS) {
            return $this->FieldAssignment();
        }

        return $this->PlainValue();
    }

    /**
     * PlainValue ::= integer | string | float | boolean | Array | Annotation
     *
     * @return mixed
     */
    public function PlainValue()
    {
        $this->lexer->skipWhitespace();

        if ($this->lexer->isNextToken(Lexer::T_OPEN_CURLY_BRACES)) {
            return $this->Arrayx();
        }

        if ($this->lexer->isNextToken(Lexer::T_AT)) {
            return $this->Annotation();
        }

        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);
                return $this->lexer->token['value'];

            case Lexer::T_INTEGER:
                $this->match(Lexer::T_INTEGER);
                return (int)$this->lexer->token['value'];

            case Lexer::T_FLOAT:
                $this->match(Lexer::T_FLOAT);
                return (float)$this->lexer->token['value'];

            case Lexer::T_TRUE:
                $this->match(Lexer::T_TRUE);
                return true;

            case Lexer::T_FALSE:
                $this->match(Lexer::T_FALSE);
                return false;

            case Lexer::T_NULL:
                $this->match(Lexer::T_NULL);
                return null;

            default:
                $this->syntaxError('PlainValue');
        }
    }

    /**
     * FieldAssignment ::= FieldName "=" PlainValue
     * FieldName ::= identifier
     *
     * @return array
     */
    public function FieldAssignment()
    {
        $this->lexer->skipWhitespace();
        $this->match(Lexer::T_IDENTIFIER);
        $fieldName = $this->lexer->token['value'];

        $this->lexer->skipWhitespace();
        $this->match(Lexer::T_EQUALS);

        $item = new \stdClass();
        $item->name  = $fieldName;
        $item->value = $this->PlainValue();

        return $item;
    }

    /**
     * Array ::= "{" ArrayEntry {"," ArrayEntry}* "}"
     *
     * @return array
     */
    public function Arrayx()
    {
        $array = $values = array();

        $this->lexer->skipWhitespace();
        $this->match(Lexer::T_OPEN_CURLY_BRACES);
        $values[] = $this->ArrayEntry();

        $this->lexer->skipWhitespace();
        while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $values[] = $this->ArrayEntry();
            $this->lexer->skipWhitespace();
        }

        $this->lexer->skipWhitespace();
        $this->match(Lexer::T_CLOSE_CURLY_BRACES);

        foreach ($values as $value) {
            list ($key, $val) = $value;

            if ($key !== null) {
                $array[$key] = $val;
            } else {
                $array[] = $val;
            }
        }

        return $array;
    }

    /**
     * ArrayEntry ::= Value | KeyValuePair
     * KeyValuePair ::= Key "=" PlainValue
     * Key ::= string | integer
     *
     * @return array
     */
    public function ArrayEntry()
    {
        $this->lexer->skipWhitespace();

        $this->lexer->resetPeek();
        while ((null !== $peak = $this->lexer->peek()) && $peak['type'] === Lexer::T_WHITESPACE);

        if ($peak['type'] === Lexer::T_EQUALS) {
            $this->match(
                $this->lexer->isNextToken(Lexer::T_INTEGER) ? Lexer::T_INTEGER : Lexer::T_STRING
            );

            $key = $this->lexer->token['value'];

            $this->lexer->skipWhitespace();
            $this->match(Lexer::T_EQUALS);

            return array($key, $this->PlainValue());
        }

        return array(null, $this->Value());
    }

    /**
     * Constructs a new annotation with a given map of values.
     *
     * The default construction procedure is to instantiate a new object of a class
     * with the same name as the annotation. Subclasses can override this method to
     * change the construction process of new annotations.
     *
     * @param string The name of the annotation.
     * @param array The map of annotation values.
     * @return mixed The new annotation with the given values.
     */
    protected function newAnnotation($name, array $values)
    {
        return new $name($values);
    }
}