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
 * Simple lexer for docblock annotations.
 *
 * This Lexer can be subclassed to customize certain aspects of the annotation
 * lexing (token recognition) process. Note though that currently no special care
 * is taken to maintain full backwards compatibility for subclasses. Implementation
 * details of the default Lexer can change without explicit notice.
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class Lexer extends \Doctrine\Common\Lexer
{
    const T_NONE                = 1;
    const T_IDENTIFIER          = 2;
    const T_INTEGER             = 3;
    const T_STRING              = 4;
    const T_FLOAT               = 5;

    const T_AT                  = 101;
    const T_CLOSE_CURLY_BRACES  = 102;
    const T_CLOSE_PARENTHESIS   = 103;
    const T_COMMA               = 104;
    const T_EQUALS              = 105;
    const T_FALSE               = 106;
    const T_NAMESPACE_SEPARATOR = 107;
    const T_OPEN_CURLY_BRACES   = 108;
    const T_OPEN_PARENTHESIS    = 109;
    const T_TRUE                = 110;
    const T_NULL                = 111;

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns()
    {
        return array(
            '[a-z_][a-z0-9_:]*',
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            '"(?:[^"]|"")*"',
            "'(?:[^']|'')*'",
        );
    }

    /**
     * @inheritdoc
     */
    protected function getNonCatchablePatterns()
    {
        return array('\s+', '\*+', '(.)');
    }

    /**
     * @inheritdoc
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;

        // Checking numeric value
        if (is_numeric($value)) {
            return (strpos($value, '.') !== false || stripos($value, 'e') !== false)
                ? self::T_FLOAT : self::T_INTEGER;
        }

        if ($value[0] === '"') {
            $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));

            return self::T_STRING;
        } else if ($value[0] === "'") {
            $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));

            return self::T_STRING;
        } else {
            switch (strtolower($value)) {
                case '@':
                    return self::T_AT;

                case ',':
                    return self::T_COMMA;

                case '(':
                    return self::T_OPEN_PARENTHESIS;

                case ')':
                    return self::T_CLOSE_PARENTHESIS;

                case '{':
                    return self::T_OPEN_CURLY_BRACES;

                case '}':
                    return self::T_CLOSE_CURLY_BRACES;

                case '=':
                    return self::T_EQUALS;

                case '\\':
                    return self::T_NAMESPACE_SEPARATOR;

                case 'true':
                    return self::T_TRUE;

                case 'false':
                    return self::T_FALSE;

                case 'null':
                    return self::T_NULL;

                default:
                    if (ctype_alpha($value[0]) || $value[0] === '_') {
                        return self::T_IDENTIFIER;
                    }

                    break;
            }
        }

        return $type;
    }
}