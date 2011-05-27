<?php

namespace Doctrine\Common\Annotations\Annotation;

final class Author
{
    public $name;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->name = $values['value'];
        }
    }
}