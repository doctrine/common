<?php

namespace Doctrine\Tests\Common\Reflection;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation(
 *   key = "value"
 * )
 */
class AnnotationClassWithScopeResolution
{

    const FOO = \stdClass::class;

  /**
   * Example with comment.
   */
    const BAR = \stdClass::class;
}
