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
 * TargetStrategy strategy for annotation @ Target
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class TargetStrategy extends MarkerStrategy
{
    private static $types = array(
        Target::TARGET_ALL,
        Target::TARGET_CLASS,
        Target::TARGET_METHOD,
        Target::TARGET_PROPERTY,
        Target::TARGET_NESTED_ANNOTATION,
    );
    
    public function run(\Reflector $target, $annotation)
    {
              
        $type = (array)$this->getMarker()->value;
        
        foreach ($type as $t) {
            if (!in_array($t, self::$types))
            {
                throw AnnotationException::semanticalError(sprintf(
                        'Invalid target type "%s" at class "%s".', 
                        $this->getMarker()->value, $this->getMarker()->getClass()->getName()
                ));
            }
        }
        
        $annotationName = $this->getMarker()->getClass()->getName();
        
        if(!in_array(Target::TARGET_ALL,$type)){
            
            if(!in_array(Target::TARGET_CLASS,$type)){
                if(self::isTargetClass($target))
                {
                    throw self::exception($this->getMarker()->getClass(), $type, $target);
                }
            }
            
            if(!in_array(Target::TARGET_METHOD,$type)){
                if(self::isTargetMethod($target))
                {
                    throw self::exception($this->getMarker()->getClass(), $type, $target);
                }
            }
            

            if(!in_array(Target::TARGET_PROPERTY,$type)){
                if(self::isTargetProperty($target))
                {
                    throw self::exception($this->getMarker()->getClass(), $type, $target);
                }
            }
            
            
            if(!in_array(Target::TARGET_NESTED_ANNOTATION,$type)){
                // TODO - do this better
                if(!self::isTargetReflecttion($target))
                {
                    throw self::exception($this->getMarker()->getClass(), $type, $target);
                }
            }
            
        }
    }
    
    
    
    
    private static function exception(\ReflectionClass $annot, array $type,\Reflector $target)
    {
        if(self::isTargetClass($target))
        {
            return AnnotationException::semanticalError(
                sprintf('Declaration of "@%s" is not compatible with annotation target [%s], at class "%s".', 
                        $annot->getShortName(), implode(", ", $type), $target->getName())
            );
        }
        elseif(self::isTargetMethod($target))
        {
            return AnnotationException::semanticalError(
                sprintf('Declaration of "@%s" is not compatible with annotation target [%s], "%s" at method "%s".', 
                        $annot->getShortName(), implode(", ", $type), $target->getDeclaringClass()->getName(), $target->getName())
            );
        }
        
        elseif(self::isTargetProperty($target))
        {
            return AnnotationException::semanticalError(
                sprintf('Declaration of "@%s" is not compatible with annotation target [%s], "%s" at property "%s".', 
                        $annot->getShortName(), implode(", ", $type), $target->getDeclaringClass()->getName(), $target->getName())
            );
        }
        else{
            return AnnotationException::semanticalError(
                sprintf('Declaration of "@%s" is not compatible with annotation target [%s].', 
                        $annot->getShortName(), implode(", ", $type))
            );
        }
    }
    
    
    /**
     * @param \Reflector $target
     * @return bool 
     */
    private static function isTargetClass(\Reflector $target)
    {
        return ($target instanceof \ReflectionClass);
    }
    
    /**
     * @param \Reflector $target
     * @return bool 
     */
    private static function isTargetProperty(\Reflector $target)
    {
        return ($target instanceof \ReflectionProperty);
    }
    
    /**
     * @param \Reflector $target
     * @return bool 
     */
    private static function isTargetMethod(\Reflector $target)
    {
        return ($target instanceof \ReflectionMethod);
    }
    
    /**
     * @param \Reflector $target
     * @return bool 
     */
    private static function isTargetReflecttion(\Reflector $target)
    {
        return self::isTargetClass($target) || self::isTargetMethod($target) || self::isTargetProperty($target);
    }
    

}