<?php

namespace Doctrine\Common\Annotations;

/**
 * Annotation that can be used to signal to the parser to ignore specific
 * annotations during the parsing process.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class IgnoreAnnotation implements IgnoreAnnotationsInterface
{
    private $names;

    public function __construct(array $values)
    {
        if (is_string($values['value'])) {
            $values['value'] = array($values['value']);
        }
        if (!is_array($values['value'])) {
            throw new \RuntimeException(sprintf('@IgnoreAnnotation expects either a string name, or an array of strings, but got %s.', json_encode($values['value'])));
        }

        $this->names = $values['value'];
    }

    public function getNames()
    {
        return $this->names;
    }
}