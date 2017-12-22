<?php
namespace Doctrine\Common;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 * Base class for writing simple lexers, i.e. for creating small DSLs.
 *
 * Lexer moved into its own Component Doctrine\Common\Lexer. This class
 * only stays for being BC.
 *
 * @since  2.0
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
abstract class Lexer extends AbstractLexer
{
}
