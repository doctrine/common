<?php

namespace Doctrine\Common\Annotations\Annotation;

final class Since
{
    public $version;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->version = $values['value'];
        }
    }
}