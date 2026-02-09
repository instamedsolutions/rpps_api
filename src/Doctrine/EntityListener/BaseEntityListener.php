<?php

namespace App\Doctrine\EntityListener;

use App\Entity\BaseEntity;
use DateTime;
use Doctrine\ORM\Event\PrePersistEventArgs;

class BaseEntityListener
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof BaseEntity) {
            return;
        }

        if (null === $entity->getCreatedDate()) {
            $entity->setCreatedDate(new DateTime());
        }
    }
}
