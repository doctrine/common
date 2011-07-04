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
 * DefaultValueStrategy strategy for annotation @Target
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class DefaultValueStrategy extends MarkerStrategy
{
    public function run(\ReflectionClass $target,$annotation)
    {
        if (!($annotation instanceof Annotation))
        {
            throw AnnotationException::semanticalError(sprintf(
                    'The class "%s" is not an annotation.', get_class($annotation)
            ));
        }
        
        $property   = $this->getMarker()->getProperty();
        $value      = $this->getMarker()->value;
        
        if($property->getValue($annotation) == null)
        {
            if($property->isPublic())
            {
                $property->setValue($annotation, $value);
            }
            else
            {
                $property->setAccessible(true);
                $property->setValue($annotation, $value);
                $property->setAccessible(false);
            }
        }
    }

}