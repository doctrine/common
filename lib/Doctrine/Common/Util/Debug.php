<?php
/*
 *  $Id$
 *
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

namespace Doctrine\Common\Util;

use Doctrine\ORM\Proxy\Proxy as ORMProxy;
use Doctrine\Common\Persistence\Proxy;

/**
 * Static class containing most used debug methods.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision: 3938 $
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 */
final class Debug
{
    /**
     * Private constructor (prevents from instantiation)
     *
     */
    private function __construct() {}

    /**
     * Determine if a given variable contains a Proxy
     * It has BC handling for Doctrine ORM 2.0/2.1
     *
     * @static
     * @param $var
     * @param null $initializationState if set, it is compared to the initialization state of the variable
     * @return bool
     */
    public static function isProxy($var, $initializationState = null)
    {
        if ($var instanceof Proxy) {
            return null === $initializationState ? true : $var->__isInitialized() === $initializationState;
        }

        if ($var instanceof ORMProxy) {
            return null === $initializationState ? true : $var->__isInitialized__ === $initializationState;
        }

        return false;
    }

    /**
     * Prints a dump of the public, protected and private properties of $var.
     *
     * @static
     * @link http://xdebug.org/
     * @param mixed $var
     * @param integer $maxDepth Maximum nesting level for object properties
     * @param boolean $stripTags Flag that indicate if output should strip HTML tags
     */
    public static function dump($var, $maxDepth = 2, $stripTags = true)
    {
        ini_set('html_errors', 'On');
        
        if (extension_loaded('xdebug')) {
            ini_set('xdebug.var_display_max_depth', $maxDepth);
        }
        
        $var = self::export($var, $maxDepth++);
        
        ob_start();
        var_dump($var);
        $dump = ob_get_contents();
        ob_end_clean();
        
        echo ($stripTags ? strip_tags(html_entity_decode($dump)) : $dump);
        
        ini_set('html_errors', 'Off');
    }
    
    public static function export($var, $maxDepth)
    {
        $return = null;
        $isObj = is_object($var);
    
        if ($isObj && in_array('Doctrine\Common\Collections\Collection', class_implements($var))) {
            $var = $var->toArray();
        }
        
        if ($maxDepth) {
            if (is_array($var)) {
                $return = array();
            
                foreach ($var as $k => $v) {
                    $return[$k] = self::export($v, $maxDepth - 1);
                }
            } else if ($isObj) {
                if ($var instanceof \DateTime) {
                    $return = $var->format('c');
                } else {
                    $reflClass = new \ReflectionClass(get_class($var));
                    $return = new \stdclass();
                    $return->{'__CLASS__'} = get_class($var);

                    if (static::isProxy($var, false)) {
                        $reflProperty = $reflClass->getProperty('_identifier');
                        $reflProperty->setAccessible(true);

                        foreach ($reflProperty->getValue($var) as $name => $value) {
                            $return->$name = self::export($value, $maxDepth - 1);
                        }
                    } else {
                        $excludeProperties = array();

                        if ($var instanceof Proxy) {
                            $excludeProperties = $var->__getInternalProperties();
                        } elseif ($var instanceof ORMProxy) {
                            $excludeProperties = array('_entityPersister', '__isInitialized__', '_identifier');
                        }

                        foreach ($reflClass->getProperties() as $reflProperty) {
                            $name  = $reflProperty->getName();

                            if ( ! in_array($name, $excludeProperties)) {
                                $reflProperty->setAccessible(true);

                                $return->$name = self::export($reflProperty->getValue($var), $maxDepth - 1);
                            }
                        }
                    }
                }
            } else {
                $return = $var;
            }
        } else {
            $return = is_object($var) ? get_class($var) 
                : (is_array($var) ? 'Array(' . count($var) . ')' : $var);
        }
        
        return $return;
    }

    public static function toString($obj)
    {
        return method_exists('__toString', $obj) ? (string) $obj : get_class($obj) . '@' . spl_object_hash($obj);
    }
}
