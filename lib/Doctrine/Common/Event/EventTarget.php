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
     * @var \Doctrine\Common\Event\EventListenerList
     */
    protected $listenerList;

    /**
     * Constructor.
     *
     */
    public function __construct(EventListenerListInterface $listenerList = null)
    {
        $this->listenerList = $listenerList ?: new EventListenerList();
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $subscriber->subscribe($this);
    }

    /**
     * {@inheritdoc}
     */
    public function removeEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $subscriber->unsubscribe($this);
    }

    /**
     * {@inheritdoc}
     */
    public function addEventListener(EventListenerInterface $listener)
    {
        $type = $listener->getType();

        if (empty($type)) {
            throw new EventException('Unspecified event type', EventException::UNSPECIFIED_EVENT_TYPE_ERR);
        }

        $this->listenerList->add($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function removeEventListener(EventListenerInterface $listener)
    {
        $type = $listener->getType();

        if (empty($type)) {
            throw new EventException('Unspecified event type', EventException::UNSPECIFIED_EVENT_TYPE_ERR);
        }

        $this->listenerList->remove($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchEvent(Event $event)
    {
        $type = $event->getType();

        if (empty($type)) {
            throw new EventException('Unspecified event type', EventException::UNSPECIFIED_EVENT_TYPE_ERR);
        }

        $listenerList = $this->listenerList->get($type);

        foreach ($listenerList as $listener) {
            // Listeners may want to modify the target. Reset every iteration
            $event->setTarget($this);

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
    public function hasEventListener($type)
    {
        if (empty($type)) {
            throw new EventException('Unspecified event type', EventException::UNSPECIFIED_EVENT_TYPE_ERR);
        }

        return $this->listenerList->size($type) > 0;
    }
}