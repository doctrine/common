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

namespace Doctrine\Common\Annotations\Marker\Strategy;

use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;
/**
 * TypeStrategy strategy for annotation Target
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class TypeStrategy extends MarkerStrategy
{
    /**
     * @param \ReflectionClass $target
     * @param Annotation $annotation 
     */
    public function run(\Reflector $target,$annotation)
    {
        if (!($annotation instanceof Annotation))
        {
            throw AnnotationException::semanticalError(sprintf(
                    'The class "%s" is not an annotation.', get_class($annotation)
            ));
        }
        
        
        if($this->getMarker()->getProperty())
        {
            $item   = $this->getMarker()->getProperty();
            $value  = $item->getValue($annotation);
        }
        elseif($this->getMarker()->getMethod())
        {
            $item   = $this->getMarker()->getMethod();
            $value  = $item->invoke($annotation);
        }
        else
        {
            throw new \InvalidArgumentException('Invalid marker.');
        }
        
        
        $nullable   = $this->getMarker()->nullable;
        $type       = $this->getMarker()->value;
        $valType    = gettype($value);
        $valid      = true;
        
        if(($value === null))
        {
            if ($nullable === false)
            {
                throw self::exception($this->getMarker()->getClass(), $item, $type, $value, $target);
            }
        }
        elseif($valType == 'object')
        {
            if(!($value instanceof $type))
            {
                throw self::exception($this->getMarker()->getClass(), $item, $type, $value, $target);
            }
        }
        else
        {
            if(gettype($value) != $type)
            {
                throw self::exception($this->getMarker()->getClass(), $item, $type, $value, $target);
            }
        }
    }
    
    
     private static function exception(\ReflectionClass $annot, \ReflectionProperty $property,$type,$value,\Reflector $target)
    {
        $valType = is_object($value) ? get_class($value) : gettype($value);
        if($target instanceof \ReflectionClass)
        {
            return AnnotationException::semanticalError(
                sprintf('Property "%s" must be an instance of "%s", "%s" given. "%s" at "@%s".', 
                        $property->getName(), $type, $valType, $target->getName(), $annot->getShortName())
            );
        }
        if($target instanceof \ReflectionMethod)
        {
            return AnnotationException::semanticalError(
                sprintf('Property "%s" must be an instance of "%s", "%s" given. "%s" method "%s" at "@%s".', 
                        $property->getName(), $type, $valType, $target->getDeclaringClass()->getName(), $target->getName(), $annot->getShortName())
            );
        }
        
        if($target instanceof \ReflectionProperty)
        {
            return AnnotationException::semanticalError(
                sprintf('Property "%s" must be an instance of "%s", "%s" given. "%s" property "%s" at "@%s".', 
                        $property->getName(), $type, $valType, $target->getDeclaringClass()->getName(), $target->getName(), $annot->getShortName())
            );
        }
        else{
            return AnnotationException::semanticalError(
                sprintf('Property "%s" must be an instance of "%s", "%s" given. "%s" at "@%s".', 
                        $property->getName(), $type, $valType, get_class($target), $annot->getShortName())
            );
        }
    }

}