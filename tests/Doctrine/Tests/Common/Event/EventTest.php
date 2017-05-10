<?php

namespace Doctrine\Tests\Common\Event;

use Doctrine\Common\Event\Event;

class EventTest extends \Doctrine\Tests\DoctrineTestCase
{
    public function testEventReadOnlyFields()
    {
        $event = new Event('foobar', true);
        $event->setCurrentTarget($this);
        $event->setTarget($this);
        $event->setData(true);

        $timestamp = microtime(true);

        $this->assertEquals('foobar', $event->getType());
        $this->assertEquals($this, $event->getCurrentTarget());
        $this->assertEquals($this, $event->getTarget());

        $this->assertTrue($timestamp > $event->getTimeStamp());
        $this->assertTrue($event->getData());
    }

    public function testEventCancellability()
    {
        $event = new Event('foobar', true);

        $this->assertTrue($event->isCancelable());

        $event = new Event('foobar', false);

        $this->assertFalse($event->isCancelable());
    }

    public function testEventPropagability()
    {
        // Cancelable event
        $event = new Event('foobar', true);

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());

        // Non-cancelable event
        $event = new Event('foobar', false);

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testEventPreventability()
    {
        // Cancelable event
        $event = new Event('foobar', true);

        $this->assertFalse($event->isDefaultPrevented());

        $event->preventDefault();

        $this->assertTrue($event->isDefaultPrevented());

        // Non-cancelable event
        $event = new Event('foobar', false);

        $this->assertFalse($event->isDefaultPrevented());

        $event->preventDefault();

        $this->assertFalse($event->isDefaultPrevented());
    }
}