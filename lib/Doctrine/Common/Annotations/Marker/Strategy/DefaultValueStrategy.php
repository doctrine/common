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
 * DefaultValueStrategy strategy for annotation @ Target
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class DefaultValueStrategy extends MarkerStrategy
{
    public function run(\Reflector $target,$annotation)
    {
        $marker = $this->getMarker();
        
        if($marker->getProperty())
        {
            $item   = $marker->getProperty();
            $value  = $item->getValue($annotation);
        }
        elseif($marker->getMethod())
        {
            $item   = $marker->getMethod();
            $value  = $item->invoke($annotation);
        }
        else
        {
            throw new \InvalidArgumentException('Invalid marker.');
        }
        
        if($value == null)
        {
            if(!$item instanceof \ReflectionProperty)
            {
                $item = $marker->getClass()->getProperty($item->getName());
            }
            
            if($item->isPublic())
            {
                $item->setValue($annotation, $marker->value);
            }
            else
            {
                $item->setAccessible(true);
                $item->setValue($annotation, $marker->value);
                $item->setAccessible(false);
            }
        }
    }
}