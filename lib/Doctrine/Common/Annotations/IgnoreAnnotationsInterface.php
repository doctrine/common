<?php

namespace Doctrine\Common\Annotations;

/**
 * Interface that must be implemented by annotation classes that are used to
 * signal to the parser to ignore certain annotations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface IgnoreAnnotationsInterface
{
    /**
     * Returns an array of raw annotation names to ignore.
     *
     * @return array
     */
    function getNames();
}