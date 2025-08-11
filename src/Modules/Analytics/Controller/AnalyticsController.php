<?php

namespace Modules\Analytics\Controller;

use Modules\Analytics\Entity\AnalyticsEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/analytics')]
class AnalyticsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/stats', name: 'analytics_stats', methods: ['GET'])]
    public function getStats(Request $request): JsonResponse
    {
        $period = $request->query->get('period', '7d');
        $startDate = $this->getStartDate($period);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('ae.eventName, COUNT(ae.id) as count')
           ->from(AnalyticsEvent::class, 'ae')
           ->where('ae.createdAt >= :startDate')
           ->setParameter('startDate', $startDate)
           ->groupBy('ae.eventName')
           ->orderBy('count', 'DESC');

        $eventStats = $qb->getQuery()->getResult();

        // Statistiques par jour
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DATE(ae.createdAt) as date, COUNT(ae.id) as count')
           ->from(AnalyticsEvent::class, 'ae')
           ->where('ae.createdAt >= :startDate')
           ->setParameter('startDate', $startDate)
           ->groupBy('date')
           ->orderBy('date', 'ASC');

        $dailyStats = $qb->getQuery()->getResult();

        return $this->json([
            'period' => $period,
            'startDate' => $startDate->format('c'),
            'eventStats' => $eventStats,
            'dailyStats' => $dailyStats,
            'totalEvents' => array_sum(array_column($eventStats, 'count'))
        ]);
    }

    #[Route('/events', name: 'analytics_track_event', methods: ['POST'])]
    public function trackEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $event = new AnalyticsEvent();
        $event->setEventName($data['eventName'] ?? 'unknown');
        $event->setUserId($data['userId'] ?? null);
        $event->setSessionId($data['sessionId'] ?? null);
        $event->setProperties($data['properties'] ?? []);
        $event->setIpAddress($request->getClientIp());
        $event->setUserAgent($request->headers->get('User-Agent'));

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Event tracked successfully',
            'eventId' => $event->getId()
        ]);
    }

    #[Route('/top-events', name: 'analytics_top_events', methods: ['GET'])]
    public function getTopEvents(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $period = $request->query->get('period', '30d');
        $startDate = $this->getStartDate($period);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('ae.eventName, COUNT(ae.id) as count')
           ->from(AnalyticsEvent::class, 'ae')
           ->where('ae.createdAt >= :startDate')
           ->setParameter('startDate', $startDate)
           ->groupBy('ae.eventName')
           ->orderBy('count', 'DESC')
           ->setMaxResults($limit);

        $topEvents = $qb->getQuery()->getResult();

        return $this->json([
            'topEvents' => $topEvents,
            'period' => $period
        ]);
    }

    private function getStartDate(string $period): \DateTimeImmutable
    {
        $now = new \DateTimeImmutable();
        
        return match($period) {
            '1d' => $now->modify('-1 day'),
            '7d' => $now->modify('-7 days'),
            '30d' => $now->modify('-30 days'),
            '90d' => $now->modify('-90 days'),
            default => $now->modify('-7 days')
        };
    }
}
