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

    public function run($annotation,$target)
    {
        if (!($annotation instanceof Annotation))
        {
            throw AnnotationException::semanticalError(sprintf(
                    'The class "%s" is not an annotation.', get_class($annotation)
            ));
        }
        
        $class  = $this->getMarkers()->getAnnotationClass();
        $marker = $this->getMarker();
        
        
        $class          = new \ReflectionClass(get_class($target));
        $type           = (array)$this->getMarker()->value;
        $annotationName = get_class($annotation);
        
        if(!in_array(Target::TARGET_ALL,$type)){
            
            if($type != Target::TARGET_CLASS){
                if($this->getMarkers()->getReader()->getClassAnnotation($class, $annotationName)){
                    throw new AnnotationException("INVALID");
                }
            }
            
            if(!in_array(Target::TARGET_METHOD,$type)){
                foreach ($class->getMethods() as $method) {
                    if($this->getMarkers()->getReader()->getMethodAnnotation($method, $annotationName)){
                        throw new AnnotationException("INVALID");
                    }
                }
            }

            if(!in_array(Target::TARGET_PROPERTY,$type)){
                foreach ($class->getProperties() as $property) {
                    if($this->getMarkers()->getReader()->getPropertyAnnotation($property, $annotationName)){
                        throw new AnnotationException("INVALID");
                    }
                }
            }
        }
        
        
        echo "\n CLASS NAME     :". $class->getName();
        echo "\n ANNOTATION     :". get_class($annotation);
        echo "\n MARKER         :". get_class($marker);
        echo "\n TARGET         :". get_class($target);
        echo "\n ---------------------\n\n\n";
    }

}