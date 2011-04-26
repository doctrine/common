<?php

namespace Doctrine\Common\Annotations;

/**
 * This annotation can be used to signal to the parser to ignore all
 * phpDocumentor annotations in the class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class IgnorePhpDoc implements IgnoreAnnotationsInterface
{
    public function getNames()
    {
        static $names = array(
            'access', 'author', 'copyright', 'deprecated', 'example', 'ignore',
            'internal', 'link', 'see', 'since', 'tutorial', 'version', 'package',
            'subpackage', 'name', 'global', 'param', 'return', 'staticvar',
            'static', 'var',
        );

        return $names;
    }
}