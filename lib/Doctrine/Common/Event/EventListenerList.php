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
 * A class that holds a list of EventListeners. A single instance can be used to hold all listeners
 * (of all types) for the instance using the list. It is the responsiblity of the class using the
 * EventListenerList to provide type-safe API and methods which dispatch event notification methods
 * to appropriate Event Listeners on the list.
 *
 * @api
 *
 * @link        www.doctrine-project.org
 * @since       2.3
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class EventListenerList
{
    /**
     * @var array
     */
    protected $listenerList = array();

    /**
     * @var array
     */
    private $sortedListenerList = array();

    /**
     * {@inheritdoc}
     */
    public function add(EventListenerInterface $listener)
    {
        $type = $listener->getType();
        $hash = spl_object_hash($listener);

        $this->listenerList[$type][$hash] = $listener;
        $this->sortedListenerList[$type]  = false;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(EventListenerInterface $listener)
    {
        $type = $listener->getType();
        $hash = spl_object_hash($listener);

        if (isset($this->listenerList[$type][$hash])) {
            unset($this->listenerList[$type][$hash]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function size($type)
    {
        return count($this->listenerList[$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($type)
    {
        if (isset($this->sortedListenerList[$type]) && ! $this->sortedListenerList[$type]) {
            uasort(
                $this->listenerList[$type],
                function ($listenerA, $listenerB)
                {
                    $priorityA = $listenerA->getPriority();
                    $priorityB = $listenerB->getPriority();

                    if ($priorityA === $priorityB) {
                        return 0;
                    }

                    // Higher the priority, first to be executed
                    return ($priorityA > $priorityB) ? -1 : 0;
                }
            );

            $this->sortedListenerList[$type] = true;
        }

        return isset($this->listenerList[$type])
            ? array_values($this->listenerList[$type])
            : array();
    }
}