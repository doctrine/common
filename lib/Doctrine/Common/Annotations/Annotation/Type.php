<?php

namespace Doctrine\Common\Annotations\Annotation;

final class Type
{
    const TYPE_INTEGER = 'integer';
    const TYPE_DOUBLE  = 'double';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY   = 'array';
    const TYPE_UNKNOWN = 'unknown';

    public $name;
    public $type;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $name = $values['value'];
            if (false !== $pos = strpos($name, ' ')) {
                $name = substr($name, 0, $pos);
            }
            $this->name = $name;

            if ('integer' === $name || 'int' === $name) {
                $this->type = self::TYPE_INTEGER;
            } else if ('double' === $name || 'float' === $name) {
                $this->type = self::TYPE_FLOAT;
            } else if ('string' === $name) {
                $this->type = self::TYPE_STRING;
            } else if ('array' === $name) {
                $this->type = self::TYPE_ARRAY;
            } else {
                $this->type = self::TYPE_UNKNOWN;
            }
        }
    }
}