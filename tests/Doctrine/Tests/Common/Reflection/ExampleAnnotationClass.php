<?php

namespace Doctrine\Tests\Common\Reflection;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation(
 *   key = "value"
 * )
 */
const foo = \stdClass::class;
class ExampleAnnotationClass {
    const foo = \stdClass::class;
} 
