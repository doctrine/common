<?php
namespace Doctrine\Common;

/**
 * Contract for classes that are potential listeners of a <tt>NotifyPropertyChanged</tt>
 * implementor.
 *
 * @link   www.doctrine-project.org
 * @since  2.0
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
interface PropertyChangedListener
{
    /**
     * Notifies the listener of a property change.
     *
     * @param object $sender       The object on which the property changed.
     * @param string $propertyName The name of the property that changed.
     * @param mixed  $oldValue     The old value of the property that changed.
     * @param mixed  $newValue     The new value of the property that changed.
     *
     * @return void
     */
    public function propertyChanged($sender, $propertyName, $oldValue, $newValue);
}
