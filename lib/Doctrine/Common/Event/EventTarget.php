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
 * Allow binding specific on its subclasses instances. Allow registration and
 * removal of {@link EventListener}s on an EventTarget and dispatch events to
 * that EventTarget.
 *
 * @api
 *
 * @abstract
 * @link        www.doctrine-project.org
 * @since       2.3
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class EventTarget implements EventTargetInterface
{
    /**
     * @var array List of EventListeners
     */
    protected $listeners = array();

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $subscriber->addEventListeners($this);
    }

    /**
     * {@inheritdoc}
     */
    public function removeEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $subscriber->removeEventListeners($target);
    }

    /**
     * {@inheritdoc}
     */
    public function addEventListener(EventListenerInterface $listener)
    {
        $hash = spl_object_hash($listener);
        $type = $listener->getType();

        $this->listeners[$type]['sorted']           = false;
        $this->listeners[$type]['listeners'][$hash] = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function removeEventListener(EventListenerInterface $listener)
    {
        $type = $listener->getType();

        if ( ! isset($this->listeners[$type])) {
            return;
        }

        $hash = spl_object_hash($listener);

        if (isset($this->listeners[$type]['listeners'][$hash])) {
            unset($this->listeners[$type]['listeners'][$hash]);

            $this->listeners[$type]['sorted'] = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchEvent(Event $event)
    {
        $type = $event->getType();

        if ( ! isset($this->listeners[$type])) {
            return $event->isDefaultPrevented();
        }

        $event->setTarget($this);

        foreach ($this->getEventListeners($type) as $listener) {
            $listener->handleEvent($event);

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $event->isDefaultPrevented();
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($type)
    {
        return isset($this->listeners[$type]) && count($this->listeners[$type]['listeners']) > 0;
    }

    /**
     * Retrieve the ordered event listeners subscribed to a given event type.
     *
     * @param string $type Event type
     *
     * @return array List of event listeners
     */
    protected function getEventListeners($type)
    {
        if ( ! isset($this->listeners[$type])) {
            return array();
        }

        if ( ! $this->listeners[$type]['sorted']) {
            uasort($this->listeners[$type]['listeners'], function ($listenerA, $listenerB) {
                $priorityA = $listenerA->getPriority();
                $priorityB = $listenerB->getPriority();

                if ($priorityA === $priorityB) {
                    return 0;
                }

                // Higher the priority, first to be executed
                return ($priorityA > $priorityB) ? -1 : 0;
            });

            $this->listeners[$type]['sorted'] = true;
        }

        $listeners = array_values($this->listeners[$type]['listeners']);

        return $listeners;
    }
}