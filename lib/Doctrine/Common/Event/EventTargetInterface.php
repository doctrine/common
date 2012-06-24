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
 * An EventTarget allows binding of specific event into its subclass instances, by supporting
 * registration and removal of {@link EventListener}s on an EventTarget and also supports
 * dispatching to that EventTarget.
 *
 * @api
 *
 * @link        www.doctrine-project.org
 * @since       2.3
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
interface EventTargetInterface
{
    /**
     * This method allows the registration of event subscribers on the event target. If an
     * EventSubscriber is added to an EventTarget while it is processing an event, it will not be
     * triggered by the current actions but may be triggered during a later stage of event flow.
     * If subscriber registers an already registered EventListener on the same EventTarget with the
     * same parameters, the duplicate instances are discarded. They do not cause the EventListener
     * to be called twice and since they are discarded they do not need to be removed with the
     * removeEventListener method.
     *
     * @api
     *
     * @param \Doctrine\Common\Event\EventSubscriberInterface $subscriber EventSubscriber instance
     */
    function addEventSubscriber(EventSubscriberInterface $subscriber);

    /**
     * This method allows the removal of event subscribers on the event target. If an
     * EventSubscriber is added to an EventTarget while it is processing an event, it will be
     * triggered by the current actions but may not be triggered during a later stage of event
     * flow.
     *
     * @api
     *
     * @param \Doctrine\Common\Event\EventSubscriberInterface $subscriber EventSubscriber instance
     */
    function removeEventSubscriber(EventSubscriberInterface $subscriber);

    /**
     * This method allows the registration of event listeners on the event target. If an
     * EventListener is added to an EventTarget while it is processing an event, it will not be
     * triggered by the current actions but may be triggered during a later stage of event flow.
     * If multiple identical EventListeners are registered on the same EventTarget with the same
     * parameters the duplicate instances are discarded. They do not cause the EventListener to be
     * called twice and since they are discarded they do not need to be removed with the
     * removeEventListener method.
     *
     * @api
     *
     * @param \Doctrine\Common\Event\EventListenerInterface $listener EventListener instance
     */
    function addEventListener(EventListenerInterface $listener);

    /**
     * This method allows the removal of event listeners from the event target. If an EventListener
     * is removed from an EventTarget while it is processing an event, it will not be triggered by
     * the current actions.
     * EventListeners can never be invoked after being removed. Calling removeEventListener with
     * arguments which do not identify any currently registered EventListener on the EventTarget
     * has no effect.
     *
     * @api
     *
     * @param \Doctrine\Common\Event\EventListenerInterface $listener EventListener instance
     */
    function removeEventListener(EventListenerInterface $listener);

    /**
     * This method allows the dispatch of events into their specific implementations.
     * The target of the event is the EventTarget on which dispatchEvent is called.
     *
     * @api
     *
     * @param \Doctrine\Common\Event\Event $event Specifies the event type, behavior, and contextual
     *                                            information to be used in processing the event.
     *
     * @return boolean The return value of dispatchEvent indicates whether any of the listeners
     *                 which handled the event called preventDefault. If preventDefault was called
     *                 the value is false, else the value is true.
     */
    function dispatchEvent(Event $event);

    /**
     * Check if EventTarget contains subscribed event listeners to the given event type.
     *
     * @param string $type Event type
     *
     * @return boolean
     */
    function hasEventListener($type);
}