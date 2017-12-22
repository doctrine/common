<?php
namespace Doctrine\Common;

/**
 * Contract for classes that provide the service of notifying listeners of
 * changes to their properties.
 *
 * @link   www.doctrine-project.org
 * @since  2.0
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
interface NotifyPropertyChanged
{
    /**
     * Adds a listener that wants to be notified about property changes.
     *
     * @param PropertyChangedListener $listener
     *
     * @return void
     */
    public function addPropertyChangedListener(PropertyChangedListener $listener);
}
