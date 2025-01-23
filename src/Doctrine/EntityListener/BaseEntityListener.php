<?php

namespace App\Doctrine\EntityListener;

use App\Entity\BaseEntity;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;

class BaseEntityListener
{
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        if (!($entity instanceof BaseEntity)) {
            return;
        }

        if (null === $entity->getCreatedDate()) {
            $entity->setCreatedDate(new DateTime());
        }
    }
}
