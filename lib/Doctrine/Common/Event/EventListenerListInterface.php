<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Event;

/**
 * An EventListenerList aholds a list of EventListeners. A single instance can be used to hold all
 * listeners (of all types) for the instance using the list. It is the responsiblity of the class
 * using the EventListenerList to provide type-safe API and methods which dispatch event
 * notification methods to appropriate Event Listeners on the list.
 *
 * @api
 *
 * @link        www.doctrine-project.org
 * @since       2.3
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
interface EventListenerListInterface
{
    /**
     * Add the listener as a listener of the specified type.
     *
     * @param \Doctrine\Common\Event\EventListenerInterface $listener   The listener to be added
     */
    function add(EventListenerInterface $listener);

    /**
     * Remove the listener as a listener of the specified type.
     *
     * @param \Doctrine\Common\Event\EventListenerInterface $listener   The listener to be removed
     */
    function remove(EventListenerInterface $listener);

    /**
     * Return the total number of listeners of the supplied type for this listener list.
     *
     * @param string $type The type of listener to be counted.
     *
     * @return integer
     */
    function size($type);

    /**
     * Return an array of all the listeners of the given type.
     *
     * @param string $type
     *
     * @return array
     */
    function get($type);
}