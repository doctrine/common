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
 * The EventListener interface is the primary method for handling events. Users implement the
 * EventListener interface and register their listener on an EventTarget using the
 * AddEventListener method. The users should also remove their EventListener from its EventTarget
 * after they have completed using the listener.
 *
 * @api
 *
 * @link        www.doctrine-project.org
 * @since       2.3
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
interface EventListenerInterface
{
    /**
     * Retrieve the event listener type.
     *
     * @api
     *
     * @return string
     */
    function getType();

    /**
     * Retrieve the execution priority this EventListener.
     * Changing EventListener default priority order means the current EventListener takes
     * higher or lower priority over the other EventListeners. Default priority is 0, which
     * means that considering a higher priority value means higher importance of execution.
     *
     * @api
     *
     * @return integer Execution priority of this EventListener.
     */
    function getPriority();

    /**
     * This method is called whenever an event occurs of the type for which the EventListener
     * interface was registered.
     *
     * @param \Doctrine\Common\Event\Event $event The Event contains contextual information
     *                                            about the event. It also contains the
     *                                            stopPropagation and preventDefault methods
     *                                            which are used in determining the event's flow
     *                                            and default action.
     */
    function handleEvent(Event $event);
}