<?php

namespace Modules\Notification\MessageHandler;

use Modules\Notification\Entity\Notification;
use Modules\Notification\Message\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsMessageHandler]
class NotificationMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HubInterface $hub
    ) {}

    public function __invoke(NotificationMessage $message): void
    {
        // Créer la notification en base
        $notification = new Notification();
        $notification->setTitle($message->getTitle());
        $notification->setMessage($message->getMessage());
        $notification->setType($message->getType());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Publier via Mercure pour les notifications en temps réel
        $update = new Update(
            'notifications',
            json_encode([
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'createdAt' => $notification->getCreatedAt()->format('c'),
                'metadata' => $message->getMetadata()
            ])
        );

        $this->hub->publish($update);
    }
}
