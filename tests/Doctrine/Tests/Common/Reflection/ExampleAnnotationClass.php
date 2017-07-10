<?php

namespace Doctrine\Tests\Common\Reflection;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation(
 *   key = "value"
 * )
 */
class ExampleAnnotationClass {

  const foo = \stdClass::class;

  /**
   * Example with comment.
   */
  const bar = \stdClass::class;

} 
