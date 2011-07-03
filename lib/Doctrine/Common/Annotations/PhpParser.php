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

use SplFileObject;

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christian Kaps <christian.kaps@mohiva.com>
 */
final class PhpParser
{
    /**
     * The token list.
     * 
     * @var array
     */
    private $tokens;

    /**
     * Parses a class.
     *
     * @param \ReflectionClass $class A <code>ReflectionClass</code> object. 
     * @return array A list with use statements in the form 
     */
    public function parseClass(\ReflectionClass $class)
    {
		if (false === $filename = $class->getFilename()) {
			return array();
		}
		
        $content = $this->getFileContent($filename, $class->getStartLine());
        $namespace = str_replace('\\', '\\\\', $class->getNamespaceName());
        $content = preg_replace('/^.*?(\bnamespace\s+' . $namespace . '\s*[;|{].*)$/s', '\\1', $content);
        $this->tokens = token_get_all('<?php ' . $content);
        
        $statements = $this->parseUseStatements($class->getNamespaceName());
        $statements = $this->canonicalize($statements);

        return $statements;
	}

    /**
     * Get the list of use statements in an canonical form.
     * 
     * It's possible to alias an use statement in the form:
     * <code>
     * use Doctrine\Common as common;
     * use common\Annotations\PHPParser as Parser;
     * </code>
     * 
     * This method resolves the second use statement to its FQN.
     * 
     * @param array $statements The list with statements to canonicalize.
     * @return array An array containing canonical use statements in the form (Alias => FQN).
     */
    public function canonicalize(array $statements)
    {
        foreach ($statements as $alias => $path) {
            $pos = strpos($path, '\\');
            $firstPart = lcfirst(substr($path, 0, $pos));
            if (!isset($statements[$firstPart])) {
                continue;
            }

            $lastPart = substr($path, $pos);
            $statements[$alias] = $statements[$firstPart] . $lastPart;
        }

        return $statements;
    }

    /**
     * Get the content of the file right up to the given line number.
     * 
     * @param string $filename The name of the file to load.
     * @param int $lineNumber The number of lines to read from file.
     * @return string The content of the file.
     */
    private function getFileContent($filename, $lineNumber)
    {
        $content = '';
        $lineCnt = 0;
        $file = new SplFileObject($filename);
        while(!$file->eof()) {
            if ($lineCnt++ == $lineNumber) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }

    /**
     * Gets the next non whitespace and non comment token.
     * 
     * @return array The token if exists, null otherwise.
     */
    private function next()
    {
        while (($token = array_shift($this->tokens))) {
            if ($token[0] === T_WHITESPACE ||
                $token[0] === T_COMMENT ||
                $token[0] === T_DOC_COMMENT) {
                
                continue;
            }

            return $token;
        }

        return null;
    }

    /**
     * Get all use statements.
     * 
     * @param string $namespaceName The namespace name of the reflected class.
     * @return array A list with all found use statements.
     */
    private function parseUseStatements($namespaceName)
    {	
        $statements = array();
        while (($token = $this->next())) {
            if ($token[0] === T_USE) {
                $statements = array_merge($statements, $this->parseUseStatement());
                continue;
            } else if ($token[0] !== T_NAMESPACE || $this->parseNamespace() != $namespaceName) {
                continue;
            }

            // Get fresh array for new namespace. This is to prevent the parser to collect the use statements
            // for a previous namespace with the same name. This is the case if a namespace is defined twice
            // or if a namespace with the same name is commented out.
            $statements = array();
        }

        return $statements;
    }

    /**
     * Get the namespace name.
     *
     * @return string The found namespace name.
     */
    private function parseNamespace()
    {	
        $namespace = '';
        while (($token = $this->next())){
            if ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR) {
                $namespace .= $token[1];
            } else {
                break;
            }
        }

        return $namespace;
    }

    /**
     * Parse a single use statement.
     * 
     * @return array A list with all found class names for a use statement.
     */
    private function parseUseStatement()
    {   
        $class = '';
        $alias = '';
        $statements = array();
        $explicitAlias = false;
        while (($token = $this->next())) {
            $isNameToken = $token[0] === T_STRING || $token[0] === T_NS_SEPARATOR;
            if (!$explicitAlias && $isNameToken) {
                $class .= $token[1];
                $alias = $token[1];
            } else if ($explicitAlias && $isNameToken) {
                $alias .= $token[1];
            } else if ($token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } else if ($token === ',') {
                $statements[lcfirst($alias)] = $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } else if ($token === ';') {
                $statements[lcfirst($alias)] = $class;
                break;
            } else {
                break;
            }
        }

        return $statements;
    }
}
