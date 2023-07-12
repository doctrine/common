<?php

namespace Doctrine\Common\Proxy\Exception;

use Doctrine\Persistence\Proxy;
use InvalidArgumentException as BaseInvalidArgumentException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * Proxy Invalid Argument Exception.
 *
 * @deprecated The InvalidArgumentException class is deprecated since doctrine/common 3.5.
 */
class InvalidArgumentException extends BaseInvalidArgumentException implements ProxyException
{
    /** @return self */
    public static function proxyDirectoryRequired()
    {
        return new self('You must configure a proxy directory. See docs for details');
    }

    /**
     * @param string $className
     * @param string $proxyNamespace
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function notProxyClass($className, $proxyNamespace)
    {
        return new self(sprintf('The class "%s" is not part of the proxy namespace "%s"', $className, $proxyNamespace));
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public static function invalidPlaceholder($name)
    {
        return new self(sprintf('Provided placeholder for "%s" must be either a string or a valid callable', $name));
    }

    /** @return self */
    public static function proxyNamespaceRequired()
    {
        return new self('You must configure a proxy namespace');
    }

    /** @return self */
    public static function unitializedProxyExpected(Proxy $proxy)
    {
        return new self(sprintf('Provided proxy of type "%s" must not be initialized.', get_class($proxy)));
    }

    /**
     * @param mixed $callback
     *
     * @return self
     */
    public static function invalidClassNotFoundCallback($callback)
    {
        $type = is_object($callback) ? get_class($callback) : gettype($callback);

        return new self(sprintf('Invalid \$notFoundCallback given: must be a callable, "%s" given', $type));
    }

    /**
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function classMustNotBeAbstract($className)
    {
        return new self(sprintf('Unable to create a proxy for an abstract class "%s".', $className));
    }

    /**
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function classMustNotBeFinal($className)
    {
        return new self(sprintf('Unable to create a proxy for a final class "%s".', $className));
    }

    /**
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return self
     */
    public static function classMustNotBeReadOnly($className)
    {
        return new self(sprintf('Unable to create a proxy for a readonly class "%s".', $className));
    }

    /** @param mixed $value */
    public static function invalidAutoGenerateMode($value): self
    {
        return new self(sprintf('Invalid auto generate mode "%s" given.', $value));
    }
}
