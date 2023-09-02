<?php

namespace App\Doctrine\EntityListener;

use App\Entity\Thing;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ThingListener
{
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        if (!($entity instanceof Thing)) {
            return;
        }

        if (null === $entity->getCreatedDate()) {
            $entity->setCreatedDate(new DateTime());
        }
    }
}
