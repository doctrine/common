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

use Doctrine\Common\Annotations\AnnotationMarkers;
use Doctrine\Common\Annotations\Annotation\Marker;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Abstract class for marker strategy
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
abstract class MarkerStrategy
{

    /**
     * @var Marker
     */
    protected $marker;
    /**
     * @var \Doctrine\Common\Annotations\AnnotationMarkers
     */
    protected $markers;

    /**
     * @return type 
     */
    public function getMarker()
    {
        return $this->marker;
    }

    /**
     * @param Marker $marker 
     */
    public function setMarker(Marker $marker)
    {
        $this->marker = $marker;
    }

    /**
     * @return AnnotationMarkers 
     */
    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * @param AnnotationMarkers $markers 
     */
    public function setMarkers($markers)
    {
        $this->markers = $markers;
    }

    /**
     * @param Marker $marker
     */
    public final function __construct(AnnotationMarkers $markers, Marker $marker)
    {
        $this->setMarker($marker);
        $this->setMarkers($markers);
    }

    /**
     * @param   Marker $marker
     * @return  MarkerStrategy 
     */
    public static function factory(AnnotationMarkers $markers,Marker $marker)
    {
        // TODO - do it better
        if ($marker instanceof Target)
        {
            return new TargetStrategy($markers,$marker);
        }

        //throw new \InvalidArgumentException();
    }

    abstract function run($annotation,$target);
}