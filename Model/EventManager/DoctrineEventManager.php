<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Model\EventManager;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use JohnRogar\MageDoctrine\Api\EventManagerInterface;

/**
 * Class DoctrineEventManager
 * @package JohnRogar\MageDoctrine\Model\EventManager
 */
class DoctrineEventManager extends EventManager implements EventManagerInterface
{

    /**
     * DoctrineEventManager constructor.
     * @param EventSubscriber[] $subscribers
     */
    public function __construct(
        array $subscribers = []
    ) {
        foreach ($subscribers as $subscriber) {
            $this->addEventSubscriber($subscriber);
        }
    }
}
