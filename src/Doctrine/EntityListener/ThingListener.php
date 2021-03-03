<?php

namespace App\Doctrine\EntityListener;

use App\Entity\Thing;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;


/**
 * Class DocumentListener
 * @package App\Doctrine\EntityListener
 */
class ThingListener
{


    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {

        $entity = $event->getEntity();

        if(!($entity instanceof Thing)) {
            return;
        }

        if(!$entity->getCreatedDate()) {
            $entity->setCreatedDate(new \DateTime());
        }
    }

}
