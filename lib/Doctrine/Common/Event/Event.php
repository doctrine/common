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
 * The Event is used to provide contextual information about an event to the handler processing
 * the event. An object which inheritd the Event is generally passed as the first parameter to an
 * event handler. More specific context information is passed to event handlers by deriving
 * additional classes from Event which contain information directly relating to the type of event
 * they accompany. These derived classes can also be passed to the event listener.
 *
 * @api
 *
 * @link        www.doctrine-project.org
 * @since       2.3
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class Event
{
    /**
     * @var string The name of the event (case-insensitive).
     */
    protected $type;

    /**
     * @var float Used to specify the time (in microsseconds relative to the epoch) at which the
     *            event was created.
     */
    protected $timestamp;

    /**
     * @var boolean Used to indicate whether or not an event can have its default action prevented.
     *              If the default action can be prevented the value is true, else the value is
     *              false.
     */
    protected $cancellable;

    /**
     * @var \Doctrine\Common\Event\EventTarget Used to indicate the EventTarget to which the event
     *                                         was originally dispatched.
     */
    protected $target;

    /**
     * @var mixed Indicate the current target whose EventListeners are currently being processed.
     */
    protected $currentTarget;

    /**
     * @var mixed Event data
     */
    protected $data;

    /**
     * @see Event::preventDefault
     *
     * @var boolean Flag holding if event is currently preventing the default execution.
     */
    protected $defaultPrevented;

    /**
     * @see Event::stopPropagation
     *
     * @var boolean Flag holding if event is assigned to stop propagating to other EventListeners.
     */
    protected $propagationStopped;

    /**
     * Constructor.
     *
     * @param string  $type         Event type
     * @param boolean $cancellable  Specifies whether or not the event's default action can be prevented.
     */
    public function __construct($type, $cancellable = false)
    {
        $this->type        = $type;
        $this->timestamp   = microtime(true);
        $this->cancellable = $cancellable;
    }

    /**
     * Retrieve the event type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieve the event timestamp.
     *
     * @return float
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Define the event target.
     *
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Retrieve the event target.
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Define the event current target.
     *
     * @param mixed $target
     */
    public function setCurrentTarget($target)
    {
        $this->currentTarget = $target;
    }

    /**
     * Retrieve the event current target.
     *
     * @return mixed
     */
    public function getCurrentTarget()
    {
        return $this->currentTarget;
    }

    /**
     * Define the event data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Retrieve the event data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Retrieve information whether the event is able to be cancelled, which means the developer
     * have the ability to prevent the default action normally taken by the implementation to be
     * executed.
     *
     * @return boolean
     */
    public function isCancellable()
    {
        return $this->cancellable;
    }

    /**
     * The stopPropagation method is used prevent further propagation of an event during event
     * flow. If this method is called by any EventListener the event will cease propagating through
     * the tree. The event will complete dispatch to all listeners on the current EventTarget
     * before event flow stops. This method may be used during any stage of event flow.
     *
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * Retrieve information whether event should stop the propagation across the listeners chain.
     *
     * @return boolean
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * If an event is cancelable, the preventDefault method is used to signify that the event is to
     * be cancelled, meaning any default action normally taken by the implementation as a result of
     * the event will not occur. If, during any stage of event flow, the preventDefault method is
     * called the event is canceled. Any default action associated with the event will not occur.
     * Calling this method for a non-cancelable event has no effect. Once preventDefault has been
     * called it will remain in effect throughout the remainder of the event's propagation. This
     * method may be used during any stage of event flow.
     *
     */
    public function preventDefault()
    {
        if ($this->cancellable) {
            $this->defaultPrevented = true;
        }
    }

    /**
     * Retrieve information if current event should block the default execution.
     *
     * @return boolean
     */
    public function isDefaultPrevented()
    {
        return $this->defaultPrevented;
    }
}