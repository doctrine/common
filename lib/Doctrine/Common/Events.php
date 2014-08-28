<?php

namespace Doctrine\Common;

class Events
{
    /**
     * The preRemove event occurs for a given entity before the respective
     * ObjectManager remove operation for that entity is executed.
     *
     * This is an entity lifecycle event.
     *
     * @var string
     */
    const preRemove = 'preRemove';

    /**
     * The postRemove event occurs for an entity after the entity has
     * been deleted. It will be invoked after the database delete operations.
     *
     * This is an entity lifecycle event.
     *
     * @var string
     */
    const postRemove = 'postRemove';

    /**
     * The prePersist event occurs for a given entity before the respective
     * ObjectManager persist operation for that entity is executed.
     *
     * This is an entity lifecycle event.
     *
     * @var string
     */
    const prePersist = 'prePersist';

    /**
     * The preUpdate event occurs before the database update operations to
     * entity data.
     *
     * This is an entity lifecycle event.
     *
     * @var string
     */
    const preUpdate = 'preUpdate';

    /**
     * The postUpdate event occurs after the database update operations to
     * entity data.
     *
     * This is an entity lifecycle event.
     *
     * @var string
     */
    const postUpdate = 'postUpdate';

    /**
     * The postLoad event occurs for an entity after the entity has been loaded
     * into the current ObjectManager from the database or after the refresh operation
     * has been applied to it.
     *
     * Note that the postLoad event occurs for an entity before any associations have been
     * initialized. Therefore it is not safe to access associations in a postLoad callback
     * or event handler.
     *
     * This is an entity lifecycle event.
     *
     * @var string
     */
    const postLoad = 'postLoad';

    /**
     * The onFlush event occurs when the ObjectManager#flush() operation is invoked,
     * after any changes to managed entities have been determined but before any
     * actual database operations are executed. The event is only raised if there is
     * actually something to do for the underlying UnitOfWork. If nothing needs to be done,
     * the onFlush event is not raised.
     *
     * @var string
     */
    const onFlush = 'onFlush';
}
