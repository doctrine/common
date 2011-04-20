<?php

namespace Doctrine\Common\Annotations;

/**
 * Represents an annotation import statement.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Import
{
    private $namespace;
    private $alias;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $values['namespace'] = $values['value'];
        }
        if (!is_string($values['namespace'])) {
            throw new \RuntimeException(sprintf('Namespace must be a string, but got %s.', json_encode($values['namespace'])));
        }
        $this->namespace = $values['namespace'];

        if (!isset($values['alias'])) {
            $values['alias'] = null;
        } else if (!is_string($values['alias'])) {
            throw new \RuntimeException(sprintf('Alias must be a string, but got %s.', json_encode($values['alias'])));
        }
        $this->alias = $values['alias'];
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getAlias()
    {
        return $this->alias;
    }
}