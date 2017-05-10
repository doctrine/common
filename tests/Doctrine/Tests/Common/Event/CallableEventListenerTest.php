<?php

namespace Doctrine\Tests\Common\Event;

use Doctrine\Common\Event\Event;
use Doctrine\Common\Event\EventListener;
use Doctrine\Common\Event\CallableEventListener;

class CallableEventListenerTest extends \Doctrine\Tests\DoctrineTestCase
{
    private function getEventListener()
    {
        $handler = function ($event)
        {
            $event->setData(true);

            return false;
        };

        $eventListener = new CallableEventListener('foobar', $handler);
        $eventListener->setTarget($this);

        return $eventListener;
    }

    public function testEventListenerReadOnlyFields()
    {
        $eventListener = $this->getEventListener();

        $this->assertEquals(0, $eventListener->getPriority());

        $eventListener->setPriority(100);

        $this->assertEquals(100, $eventListener->getPriority());

        $this->assertEquals('foobar', $eventListener->getType());

        $eventListener->setType('foo');

        $this->assertEquals('foo', $eventListener->getType());
    }

    public function testEventListenerHandlingEvents()
    {
        $eventListener = $this->getEventListener();
        $event         = new Event('foobar');

        $this->assertNull($event->getData());

        $eventListener->handleEvent($event);

        $this->assertTrue($event->getData());
    }

    public function testEventListenerHandlingReturns()
    {
        $eventListener = $this->getEventListener();
        $event         = new Event('foobar', true);

        $this->assertFalse($event->isDefaultPrevented());

        $eventListener->handleEvent($event);

        $this->assertTrue($event->isDefaultPrevented());
    }

    public function testEventListenerHandlingNonCancelableEventReturns()
    {
        $eventListener = $this->getEventListener();
        $event         = new Event('foobar', false);

        $this->assertFalse($event->isDefaultPrevented());

        $eventListener->handleEvent($event);

        $this->assertFalse($event->isDefaultPrevented());
    }

    public function testEventListenerDefiningCurrentTarget()
    {
        $eventListener = $this->getEventListener();
        $event         = new Event('foobar');

        $this->assertNull($event->getCurrentTarget());

        $eventListener->handleEvent($event);

        $this->assertEquals($this, $event->getCurrentTarget());
    }
}