<?php
namespace Doctrine\Common\Persistence\Mapping;

/**
 * A MappingException indicates that something is wrong with the mapping setup.
 *
 * @since 2.2
 */
class MappingException extends \Exception
{
    /**
     * @param string $className
     * @param array  $namespaces
     *
     * @return self
     */
    public static function classNotFoundInNamespaces($className, $namespaces)
    {
        return new self("The class '" . $className . "' was not found in the " .
            "chain configured namespaces " . implode(", ", $namespaces));
    }

    /**
     * @return self
     */
    public static function pathRequired()
    {
        return new self("Specifying the paths to your entities is required " .
            "in the AnnotationDriver to retrieve all class names.");
    }

    /**
     * @param string|null $path
     *
     * @return self
     */
    public static function fileMappingDriversRequireConfiguredDirectoryPath($path = null)
    {
        if ( ! empty($path)) {
            $path = '[' . $path . ']';
        }

        return new self(
            'File mapping drivers must have a valid directory path, ' .
            'however the given path ' . $path . ' seems to be incorrect!'
        );
    }

    /**
     * @param string $entityName
     * @param string $fileName
     *
     * @return self
     */
    public static function mappingFileNotFound($entityName, $fileName)
    {
        return new self("No mapping file found named '$fileName' for class '$entityName'.");
    }

    /**
     * @param string $entityName
     * @param string $fileName
     *
     * @return self
     */
    public static function invalidMappingFile($entityName, $fileName)
    {
        return new self("Invalid mapping file '$fileName' for class '$entityName'.");
    }

    /**
     * @param string $className
     *
     * @return self
     */
    public static function nonExistingClass($className)
    {
        return new self("Class '$className' does not exist");
    }
}
