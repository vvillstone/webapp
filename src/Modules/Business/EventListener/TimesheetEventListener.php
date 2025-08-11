<?php

namespace Modules\Business\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Modules\Business\Entity\Timesheet;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class TimesheetEventListener implements EventSubscriberInterface
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
        
        if (!$entity instanceof Timesheet) {
            return;
        }

        $this->publishMercureEvent('timesheet.created', $entity, [
            'action' => 'created',
            'employee' => $entity->getEmployeeName(),
            'site' => $entity->getSiteName(),
            'client' => $entity->getClientName(),
            'date' => $entity->getDate()->format('Y-m-d'),
            'hours' => $entity->getHoursWorked(),
            'status' => $entity->getStatus()
        ]);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$entity instanceof Timesheet) {
            return;
        }

        $this->publishMercureEvent('timesheet.updated', $entity, [
            'action' => 'updated',
            'employee' => $entity->getEmployeeName(),
            'site' => $entity->getSiteName(),
            'client' => $entity->getClientName(),
            'date' => $entity->getDate()->format('Y-m-d'),
            'hours' => $entity->getHoursWorked(),
            'status' => $entity->getStatus()
        ]);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$entity instanceof Timesheet) {
            return;
        }

        $this->publishMercureEvent('timesheet.deleted', $entity, [
            'action' => 'deleted',
            'employee' => $entity->getEmployeeName(),
            'site' => $entity->getSiteName(),
            'client' => $entity->getClientName(),
            'date' => $entity->getDate()->format('Y-m-d')
        ]);
    }

    private function publishMercureEvent(string $topic, Timesheet $timesheet, array $data): void
    {
        $eventData = [
            'id' => $timesheet->getId(),
            'type' => 'timesheet',
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
