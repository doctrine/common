<?php

namespace Doctrine\Common\Proxy\Exception;

use OutOfBoundsException as BaseOutOfBoundsException;

use function sprintf;

/**
 * Proxy Invalid Argument Exception.
 *
 * @deprecated The OutOfBoundsException class is deprecated since doctrine/common 3.5.
 */
class OutOfBoundsException extends BaseOutOfBoundsException implements ProxyException
{
    /**
     * @param string $className
     * @param string $idField
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function missingPrimaryKeyValue($className, $idField)
    {
        return new self(sprintf('Missing value for primary key %s on %s', $idField, $className));
    }
}
