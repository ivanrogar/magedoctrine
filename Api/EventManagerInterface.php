<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Api;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

/**
 * Interface EventManagerInterface
 * @package JohnRogar\MageDoctrine\Api
 */
interface EventManagerInterface
{

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string         $eventName The name of the event to dispatch. The name of the event is
     *                                  the name of the method that is invoked on listeners.
     * @param EventArgs|null $eventArgs The event arguments to pass to the event handlers/listeners.
     *                                  If not supplied, the single empty EventArgs instance is used.
     *
     * @return void
     */
    public function dispatchEvent($eventName, ?EventArgs $eventArgs = null);

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string|null $event The name of the event.
     *
     * @return object[]|object[][] The event listeners for the specified event, or all event listeners.
     */
    public function getListeners($event = null);

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $event
     *
     * @return bool TRUE if the specified event has any listeners, FALSE otherwise.
     */
    public function hasListeners($event);

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string|string[] $events   The event(s) to listen on.
     * @param object          $listener The listener object.
     *
     * @return void
     */
    public function addEventListener($events, $listener);

    /**
     * Removes an event listener from the specified events.
     *
     * @param string|string[] $events
     * @param object          $listener
     *
     * @return void
     */
    public function removeEventListener($events, $listener);

    /**
     * Adds an EventSubscriber. The subscriber is asked for all the events it is
     * interested in and added as a listener for these events.
     *
     * @param EventSubscriber $subscriber The subscriber.
     *
     * @return void
     */
    public function addEventSubscriber(EventSubscriber $subscriber);

    /**
     * Removes an EventSubscriber. The subscriber is asked for all the events it is
     * interested in and removed as a listener for these events.
     *
     * @param EventSubscriber $subscriber The subscriber.
     *
     * @return void
     */
    public function removeEventSubscriber(EventSubscriber $subscriber);
}
