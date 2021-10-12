<?php

namespace Doctrine\Common\Util;

use Doctrine\Persistence\Proxy;
use ReflectionClass;

use function get_class;
use function get_parent_class;
use function ltrim;
use function rtrim;
use function strrpos;
use function substr;

/**
 * Class and reflection related functionality for objects that
 * might or not be proxy objects at the moment.
 */
class ClassUtils
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @param string $className
     *
     * @return string
     *
     * @template T of object
     * @psalm-param class-string<Proxy<T>>|class-string<T> $className
     * @psalm-return class-string<T>
     */
    public static function getRealClass($className)
    {
        $pos = strrpos($className, '\\' . Proxy::MARKER . '\\');

        if ($pos === false) {
            /** @psalm-var class-string<T> */
            return $className;
        }

        return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * Gets the real class name of an object (even if its a proxy).
     *
     * @param object $object
     *
     * @return string
     *
     * @template T of object
     * @psalm-param Proxy<T>|T $object
     * @psalm-return class-string<T>
     */
    public static function getClass($object)
    {
        return self::getRealClass(get_class($object));
    }

    /**
     * Gets the real parent class name of a class or object.
     *
     * @param string $className
     *
     * @return string
     *
     * @psalm-param class-string $className
     * @psalm-return class-string
     */
    public static function getParentClass($className)
    {
        return get_parent_class(self::getRealClass($className));
    }

    /**
     * Creates a new reflection class.
     *
     * @param string $className
     *
     * @return ReflectionClass
     *
     * @psalm-param class-string $className
     */
    public static function newReflectionClass($className)
    {
        return new ReflectionClass(self::getRealClass($className));
    }

    /**
     * Creates a new reflection object.
     *
     * @param object $object
     *
     * @return ReflectionClass
     */
    public static function newReflectionObject($object)
    {
        return self::newReflectionClass(self::getClass($object));
    }

    /**
     * Given a class name and a proxy namespace returns the proxy name.
     *
     * @param string $className
     * @param string $proxyNamespace
     *
     * @return string
     *
     * @psalm-param class-string $className
     * @psalm-return class-string
     */
    public static function generateProxyClassName($className, $proxyNamespace)
    {
        return rtrim($proxyNamespace, '\\') . '\\' . Proxy::MARKER . '\\' . ltrim($className, '\\');
    }
}
