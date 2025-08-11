<?php

namespace Modules\Notification\Controller;

use Modules\Notification\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/mark-read/{id}', name: 'notification_mark_read', methods: ['PUT'])]
    public function markAsRead(Notification $notification): JsonResponse
    {
        $notification->setIsRead(true);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Notification marked as read',
            'notification' => [
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'isRead' => $notification->isRead(),
                'readAt' => $notification->getReadAt()
            ]
        ]);
    }

    #[Route('/unread-count', name: 'notification_unread_count', methods: ['GET'])]
    public function getUnreadCount(): JsonResponse
    {
        $count = $this->entityManager->getRepository(Notification::class)
            ->count(['isRead' => false]);

        return $this->json(['unreadCount' => $count]);
    }

    #[Route('/mark-all-read', name: 'notification_mark_all_read', methods: ['PUT'])]
    public function markAllAsRead(): JsonResponse
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(Notification::class, 'n')
           ->set('n.isRead', ':isRead')
           ->set('n.readAt', ':readAt')
           ->where('n.isRead = :false')
           ->setParameter('isRead', true)
           ->setParameter('readAt', new \DateTimeImmutable())
           ->setParameter('false', false);

        $qb->getQuery()->execute();

        return $this->json(['message' => 'All notifications marked as read']);
    }
}
