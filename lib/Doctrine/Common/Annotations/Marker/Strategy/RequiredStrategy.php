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
 * RequiredStrategy strategy for annotation @Target
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class RequiredStrategy extends MarkerStrategy
{
    public function run(\Reflector $target,$annotation)
    {
        
        $property   = $this->getMarker()->getProperty();
        $value      = $property->getValue($annotation);
        $nullable   = $this->getMarker()->value;
        
        
        if(($value === null))
        {
            if ($nullable != true)
            {
                throw self::exception($this->getMarker()->getClass(), $property, $target);
            }
        }
    }
    
     private static function exception(\ReflectionClass $annot, \Reflector $property,\Reflector $target)
    {
        if($target instanceof \ReflectionClass)
        {
            return AnnotationException::semanticalError(
                sprintf('Property "%s" can not be null. "%s" at "@%s".', 
                        $property->getName(), $target->getName(), $annot->getShortName())
            );
        }
        if($target instanceof \ReflectionMethod)
        {
            return AnnotationException::semanticalError(
                sprintf('Property "%s" can not be null. "%s" method "%s" at "@%s".', 
                        $property->getName(), $target->getDeclaringClass()->getName(), $target->getName(), $annot->getShortName())
            );
        }
        
        if($target instanceof \ReflectionProperty)
        {
            return AnnotationException::semanticalError(
                sprintf('Property "%s" can not be null. "%s" property "%s" at "@%s".', 
                        $property->getName(), $target->getDeclaringClass()->getName(), $target->getName(), $annot->getShortName())
            );
        }
        else{
            return AnnotationException::semanticalError(
                sprintf('Property "%s" can not be null. "%s" at "@%s".', 
                        $property->getName(), get_class($target), $annot->getShortName())
            );
        }
    }


}