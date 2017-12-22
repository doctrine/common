<?php
namespace Doctrine\Common\Reflection;

/**
 * Finds a class in a PSR-0 structure.
 *
 * @author Karoly Negyesi <karoly@negyesi.net>
 */
interface ClassFinderInterface
{
    /**
     * Finds a class.
     *
     * @param string $class The name of the class.
     *
     * @return string|null The name of the class or NULL if not found.
     */
    public function findFile($class);
}
