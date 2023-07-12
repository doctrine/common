<?php

namespace Doctrine\Common\Proxy\Exception;

use Throwable;
use UnexpectedValueException as BaseUnexpectedValueException;

use function sprintf;

/**
 * Proxy Unexpected Value Exception.
 *
 * @deprecated The UnexpectedValueException class is deprecated since doctrine/common 3.5.
 */
class UnexpectedValueException extends BaseUnexpectedValueException implements ProxyException
{
    /**
     * @param string $proxyDirectory
     *
     * @return self
     */
    public static function proxyDirectoryNotWritable($proxyDirectory)
    {
        return new self(sprintf('Your proxy directory "%s" must be writable', $proxyDirectory));
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function invalidParameterTypeHint(
        $className,
        $methodName,
        $parameterName,
        ?Throwable $previous = null
    ) {
        return new self(
            sprintf(
                'The type hint of parameter "%s" in method "%s" in class "%s" is invalid.',
                $parameterName,
                $methodName,
                $className
            ),
            0,
            $previous
        );
    }

    /**
     * @param string $className
     * @param string $methodName
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function invalidReturnTypeHint($className, $methodName, ?Throwable $previous = null)
    {
        return new self(
            sprintf(
                'The return type of method "%s" in class "%s" is invalid.',
                $methodName,
                $className
            ),
            0,
            $previous
        );
    }
}
