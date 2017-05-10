<?php

namespace Doctrine\Tests\Common\Event;

use Doctrine\Common\Event\Event;
use Doctrine\Common\Event\EventListener;

class EventListenerTest extends \Doctrine\Tests\DoctrineTestCase
{
    private function getEventListener()
    {
        return new ExecutableEventListener();
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
}

class ExecutableEventListener extends EventListener
{
    protected $type = 'foobar';

    function handleEvent(Event $event)
    {
        $event->setData(true);
    }
}