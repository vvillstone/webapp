<?php

namespace Modules\Business\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Modules\Business\Entity\Site;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class SiteEventListener implements EventSubscriberInterface
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$entity instanceof Site) {
            return;
        }

        $this->publishMercureEvent('site.created', $entity, [
            'action' => 'created',
            'name' => $entity->getName(),
            'client' => $entity->getClientName(),
            'address' => $entity->getFullAddress(),
            'status' => $entity->getStatus()
        ]);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$entity instanceof Site) {
            return;
        }

        $this->publishMercureEvent('site.updated', $entity, [
            'action' => 'updated',
            'name' => $entity->getName(),
            'client' => $entity->getClientName(),
            'address' => $entity->getFullAddress(),
            'status' => $entity->getStatus()
        ]);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$entity instanceof Site) {
            return;
        }

        $this->publishMercureEvent('site.deleted', $entity, [
            'action' => 'deleted',
            'name' => $entity->getName(),
            'client' => $entity->getClientName(),
            'address' => $entity->getFullAddress()
        ]);
    }

    private function publishMercureEvent(string $topic, Site $site, array $data): void
    {
        $eventData = [
            'id' => $site->getId(),
            'type' => 'site',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'data' => $data
        ];

        $update = new Update(
            $topic,
            $this->serializer->serialize($eventData, 'json'),
            true
        );

        $this->hub->publish($update);
    }
}
