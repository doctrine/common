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
 * TargetStrategy strategy for annotation @Target
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
    public function run(\ReflectionClass $target,$annotation)
    {
        if (!($annotation instanceof Annotation))
        {
            throw AnnotationException::semanticalError(sprintf(
                    'The class "%s" is not an annotation.', get_class($annotation)
            ));
        }
        
        
        $types = array(
            Target::TARGET_ALL,
            Target::TARGET_CLASS,
            Target::TARGET_METHOD,
            Target::TARGET_PROPERTY,
            Target::TARGET_NESTED_ANNOTATION,
        );
        
        if (!in_array($this->getMarker()->value, self::$types))
        {
            throw AnnotationException::semanticalError(sprintf(
                    'Invalid target type "%s" at class "%s".', $this->getMarker()->value, $target->getName()
            ));
        }
        
        
        $type           = $this->getMarker()->value;
        $annotationName = $this->getMarker()->getClass()->getName();
        

        if(Target::TARGET_ALL != $type){
            
            if(Target::TARGET_CLASS == $type){
                foreach ($target->getMethods() as $method) {
                    if($this->getMarkers()->getReader()->getMethodAnnotation($method, $annotationName)){
                        throw AnnotationException::semanticalError(
                            sprintf('Annotation "%s" can not be used at method "%s"', $annotationName,$method->getName())
                        );
                    }
                }
                
                foreach ($target ->getProperties() as $property) {
                    if($this->getMarkers()->getReader()->getPropertyAnnotation($property, $annotationName)){
                        throw AnnotationException::semanticalError(
                            sprintf('Annotation "%s" can not be used at property "%s"', $annotationName,$property->getName())
                        );
                    }
                }
            }
            
            
            if(Target::TARGET_METHOD == $type){
                if($this->getMarkers()->getReader()->getClassAnnotation($target , $annotationName)){
                    throw AnnotationException::semanticalError(
                        sprintf('Annotation "%s" can not be used at class "%s"', $annotationName,$target ->getName())
                    );
                }
                
                foreach ($target ->getProperties() as $property) {
                    if($this->getMarkers()->getReader()->getPropertyAnnotation($property, $annotationName)){
                        throw AnnotationException::semanticalError(
                            sprintf('Annotation "%s" can not be used at property "%s"', $annotationName,$property->getName())
                        );
                    }
                }
            }
            

            if(Target::TARGET_PROPERTY == $type){
                if($this->getMarkers()->getReader()->getClassAnnotation($target , $annotationName)){
                    throw AnnotationException::semanticalError(
                        sprintf('Annotation "%s" can not be used at class "%s"', $annotationName,$target ->getName())
                    );
                }
                foreach ($target ->getMethods() as $method) {
                    if($this->getMarkers()->getReader()->getMethodAnnotation($method, $annotationName)){
                        throw AnnotationException::semanticalError(
                            sprintf('Annotation "%s" can not be used at method "%s"', $annotationName,$method->getName())
                        );
                    }
                }
            }
            
            
            if(Target::TARGET_NESTED_ANNOTATION == $type){
                if($this->getMarkers()->getReader()->getClassAnnotation($target , $annotationName)){
                    throw AnnotationException::semanticalError(
                        sprintf('Annotation "%s" can not be used at class "%s"', $annotationName,$target ->getName())
                    );
                }
                foreach ($target ->getMethods() as $method) {
                    if($this->getMarkers()->getReader()->getMethodAnnotation($method, $annotationName)){
                        throw AnnotationException::semanticalError(
                            sprintf('Annotation "%s" can not be used at method "%s"', $annotationName,$method->getName())
                        );
                    }
                }
                foreach ($target ->getProperties() as $property) {
                    if($this->getMarkers()->getReader()->getPropertyAnnotation($property, $annotationName)){
                        throw AnnotationException::semanticalError(
                            sprintf('Annotation "%s" can not be used at property "%s"', $annotationName,$property->getName())
                        );
                    }
                }
            }
            
            
        }
    }
    
    

}