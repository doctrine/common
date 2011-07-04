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

use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\Annotation\Marker;
use Doctrine\Common\Annotations\Marker\MarkerStrategy;
use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;
use \ReflectionProperty;

/**
 * ClassMarker Read Class Markers
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ClassMarker
{

    /**
     * @var ReflectionClass
     */
    private $class;
    /**
     * @var Reader 
     */
    private $reader;
    /**
     * @var array
     */
    private $markers;

    /**
     * @param \ReflectionClass $class
     * @param Reader $reader 
     */
    public function __construct(\ReflectionClass $class, Reader $reader)
    {
        $this->class = $class;
        $this->reader = $reader;
    }

    /**
     * @param   array $annotations
     * @return  array
     */
    public function runMarkers(array $annotations)
    {
        $list = array();
        foreach ($annotations as $annotation) {
            $marker = $this->getMarker(get_class($annotation));
            foreach ($marker->getAllMarkers() as $m) {
                $list[] = $marker->runMarker($annotation,$this->class, $m);
            }
        }
        return $list;
    }

    /**
     * @param   string $annotationClass
     * @return  AnnotationMarkers
     */
    private function getMarker($annotationClass)
    {
        if (!isset($this->markers[$annotationClass]))
        {
            $marker = new AnnotationMarkers(new ReflectionClass($annotationClass), $this->reader);
            $this->markers[$annotationClass] = $marker;
        }
        return $this->markers[$annotationClass];
    }

}
