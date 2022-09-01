<?php

namespace App\Doctrine\EntityListener;

use DateTime;
use App\Entity\Thing;
use Doctrine\ORM\Event\LifecycleEventArgs;



class ThingListener
{


    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        if (!($entity instanceof Thing)) {
            return;
        }

        if ($entity->getCreatedDate() === null) {
            $entity->setCreatedDate(new DateTime());
        }
    }

}
