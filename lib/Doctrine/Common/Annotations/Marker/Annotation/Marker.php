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

namespace Doctrine\Common\Annotations\Marker\Annotation;

use Doctrine\Common\Annotations\Annotation\Annotation;

/**
 * Base class for Annotations Markers
 *
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 * 
 */
abstract class Marker
{
    /**
     * @var \ReflectionClass
     */
    private $class;
    /**
     * @var \ReflectionProperty
     */
    private $property;

    /**
     * @return \ReflectionClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param \ReflectionClass $class 
     */
    public function setClass(\ReflectionClass $class)
    {
        $this->class = $class;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param \ReflectionProperty $property 
     */
    public function setProperty(\ReflectionProperty$property)
    {
        $this->property = $property;
    }

    /**
     * Property accessor in marker class.
     *
     * @param string $name Unknown property name
     */
    public function __get($name)
    {
        if (!isset($this->$name))
        {
            return null;
        }
        return $this->$name;
    }
    
    /**
     * @return string the marker strategy class name
     */
    public abstract function strategyClass();
    
    
    /**
     * @return integer the marker priority
     */
    public abstract function priority();
}