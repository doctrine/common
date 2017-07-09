<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventArgs;

class EventManagerTest extends \Doctrine\Tests\DoctrineTestCase
{
    /* Some pseudo events */
    const preFoo = 'preFoo';
    const postFoo = 'postFoo';
    const preBar = 'preBar';
    const postBar = 'postBar';

    private $_preFooInvoked = false;
    private $_postFooInvoked = false;

    private $_eventManager;

    protected function setUp(): void
    {
        $this->_eventManager = new EventManager;
        $this->_preFooInvoked = false;
        $this->_postFooInvoked = false;
    }

    public function testInitialState(): void
    {
        $this->assertEquals([], $this->_eventManager->getListeners());
        $this->assertFalse($this->_eventManager->hasListeners(self::preFoo));
        $this->assertFalse($this->_eventManager->hasListeners(self::postFoo));
    }

    public function testAddEventListener(): void
    {
        $this->_eventManager->addEventListener(['preFoo', 'postFoo'], $this);
        $this->assertTrue($this->_eventManager->hasListeners(self::preFoo));
        $this->assertTrue($this->_eventManager->hasListeners(self::postFoo));
        $this->assertEquals(1, count($this->_eventManager->getListeners(self::preFoo)));
        $this->assertEquals(1, count($this->_eventManager->getListeners(self::postFoo)));
        $this->assertEquals(2, count($this->_eventManager->getListeners()));
    }

    public function testDispatchEvent(): void
    {
        $this->_eventManager->addEventListener(['preFoo', 'postFoo'], $this);
        $this->_eventManager->dispatchEvent(self::preFoo);
        $this->assertTrue($this->_preFooInvoked);
        $this->assertFalse($this->_postFooInvoked);
    }

    public function testRemoveEventListener(): void
    {
        $this->_eventManager->addEventListener(['preBar'], $this);
        $this->assertTrue($this->_eventManager->hasListeners(self::preBar));
        $this->_eventManager->removeEventListener(['preBar'], $this);
        $this->assertFalse($this->_eventManager->hasListeners(self::preBar));
    }

    public function testAddEventSubscriber(): void
    {
        $eventSubscriber = new TestEventSubscriber();
        $this->_eventManager->addEventSubscriber($eventSubscriber);
        $this->assertTrue($this->_eventManager->hasListeners(self::preFoo));
        $this->assertTrue($this->_eventManager->hasListeners(self::postFoo));
    }

    /* Listener methods */

    public function preFoo(EventArgs $e): void
    {
        $this->_preFooInvoked = true;
    }

    public function postFoo(EventArgs $e): void
    {
        $this->_postFooInvoked = true;
    }
}

class TestEventSubscriber implements \Doctrine\Common\EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return ['preFoo', 'postFoo'];
    }
}
