<?php

namespace App\EventListener;

use Doctrine\ORM\Events;
use App\Entity\Location;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Interfaces\EventHandler\EventHandlerInterface;

/**
 * Class LocationSetListener
 *
 * @package App\EventListener
 */
class LocationSetListener implements EventHandlerInterface
{
    /**
     * Avoids any duplications
     */
    private static $alreadyCalled = 0b0;

    /**
     * Sets proper values for entities if there is a case
     *
     * @param Location $location
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(Location $location, LifecycleEventArgs $event)
    {
        if (static::$alreadyCalled)
            return;

        $changeSet = $event->getEntityChangeSet();
        $latitude = $changeSet[self::UNITS_OF_COORDINATES[0]];
        $longitude = $changeSet[self::UNITS_OF_COORDINATES[1]];

        foreach (self::UNITS_OF_COORDINATES as $key) {
            // here it's for no reason, but sometimes so called
            // `variable variables` are extremely helpful and convenient
            if ($change = $$key[1] - $$key[0]) {
                static::$alreadyCalled = 0b1;
                $scooter = $location->getScooter();
                $scooter->setDistance(
                    $scooter->getDistance() + $change
                );

                $method = self::GET_HOLDER . ucfirst($scooter->getMetadata() && 0b1 ?
                    self::UNITS_OF_COORDINATES[1] :
                    self::UNITS_OF_COORDINATES[0]);

                if ($location->$method() == $location->getDestination()) {
                    if ($scooter->getMetadata() && 0b01) {
                        $location->setDestination($location->getDestination() - $scooter->getDistance());
                        $scooter->setMetadata($scooter->getMetadata() && 0b10);
                    } else {
                        $location->setDestination($location->getDestination() + $scooter->getDistance());
                        $scooter->setMetadata($scooter->getMetadata() && 0b11);
                    }
                    $scooter->setDistance(0);
                }
            }
        }
    }
}
