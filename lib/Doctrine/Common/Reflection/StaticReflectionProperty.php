<?php

namespace Doctrine\Common\Reflection;

use ReflectionProperty;
use ReflectionException;

class StaticReflectionProperty extends ReflectionProperty
{
    /**
     * The PSR-0 parser object.
     *
     * @var StaticReflectionParser
     */
    protected $staticReflectionParser;

    /**
     * The name of the property.
     *
     * @var string
     */
    protected $propertyName;

    public function __construct($StaticReflectionParser, $propertyName)
    {
        $this->staticReflectionParser = $StaticReflectionParser;
        $this->propertyName = $propertyName;
    }
    public function getName()
    {
        return $this->propertyName;
    }
    protected function getStaticReflectionParser()
    {
        return $this->staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', $this->propertyName);
    }
    public function getDeclaringClass()
    {
        return $this->getStaticReflectionParser()->getReflectionClass();
    }
    public function getDocComment()
    {
        return $this->getStaticReflectionParser()->getDoxygen('property', $this->propertyName);
    }
    public function getUseStatements()
    {
        return $this->getStaticReflectionParser()->getUseStatements();
    }
    public static function export ($class, $name, $return = false) { throw new ReflectionException('Method not implemented'); }
    public function getModifiers() { throw new ReflectionException('Method not implemented'); }
    public function getValue($object = NULL) { throw new ReflectionException('Method not implemented'); }
    public function isDefault() { throw new ReflectionException('Method not implemented'); }
    public function isPrivate() { throw new ReflectionException('Method not implemented'); }
    public function isProtected() { throw new ReflectionException('Method not implemented'); }
    public function isPublic() { throw new ReflectionException('Method not implemented'); }
    public function isStatic() { throw new ReflectionException('Method not implemented'); }
    public function setAccessible ($accessible) { throw new ReflectionException('Method not implemented'); }
    public function setValue ($object, $value = NULL) { throw new ReflectionException('Method not implemented'); }
    public function __toString() { throw new ReflectionException('Method not implemented'); }
}
