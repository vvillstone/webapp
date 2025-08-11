<?php

namespace Modules\Business\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/mercure-test')]
class MercureTestController extends AbstractController
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer
    ) {}

    #[Route('/timesheet-created', name: 'mercure_test_timesheet_created', methods: ['POST'])]
    public function testTimesheetCreated(): JsonResponse
    {
        $eventData = [
            'id' => 999,
            'type' => 'timesheet',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'data' => [
                'action' => 'created',
                'employee' => 'John Doe',
                'site' => 'Test Site',
                'client' => 'Test Client',
                'date' => '2024-01-15',
                'hours' => 8.0,
                'status' => 'draft'
            ]
        ];

        $update = new Update(
            'timesheet.created',
            $this->serializer->serialize($eventData, 'json'),
            true
        );

        $this->hub->publish($update);

        return $this->json([
            'message' => 'Timesheet created event published',
            'data' => $eventData
        ]);
    }

    #[Route('/timesheet-updated', name: 'mercure_test_timesheet_updated', methods: ['POST'])]
    public function testTimesheetUpdated(): JsonResponse
    {
        $eventData = [
            'id' => 999,
            'type' => 'timesheet',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'data' => [
                'action' => 'updated',
                'employee' => 'Jane Smith',
                'site' => 'Updated Site',
                'client' => 'Updated Client',
                'date' => '2024-01-16',
                'hours' => 7.5,
                'status' => 'approved'
            ]
        ];

        $update = new Update(
            'timesheet.updated',
            $this->serializer->serialize($eventData, 'json'),
            true
        );

        $this->hub->publish($update);

        return $this->json([
            'message' => 'Timesheet updated event published',
            'data' => $eventData
        ]);
    }

    #[Route('/site-created', name: 'mercure_test_site_created', methods: ['POST'])]
    public function testSiteCreated(): JsonResponse
    {
        $eventData = [
            'id' => 888,
            'type' => 'site',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'data' => [
                'action' => 'created',
                'name' => 'New Test Site',
                'client' => 'Test Client',
                'address' => '123 Test Street, Test City, 12345',
                'status' => 'active'
            ]
        ];

        $update = new Update(
            'site.created',
            $this->serializer->serialize($eventData, 'json'),
            true
        );

        $this->hub->publish($update);

        return $this->json([
            'message' => 'Site created event published',
            'data' => $eventData
        ]);
    }

    #[Route('/site-updated', name: 'mercure_test_site_updated', methods: ['POST'])]
    public function testSiteUpdated(): JsonResponse
    {
        $eventData = [
            'id' => 888,
            'type' => 'site',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'data' => [
                'action' => 'updated',
                'name' => 'Updated Test Site',
                'client' => 'Updated Client',
                'address' => '456 Updated Street, Updated City, 54321',
                'status' => 'maintenance'
            ]
        ];

        $update = new Update(
            'site.updated',
            $this->serializer->serialize($eventData, 'json'),
            true
        );

        $this->hub->publish($update);

        return $this->json([
            'message' => 'Site updated event published',
            'data' => $eventData
        ]);
    }

    #[Route('/all-events', name: 'mercure_test_all_events', methods: ['POST'])]
    public function testAllEvents(): JsonResponse
    {
        $events = [
            'timesheet.created' => [
                'id' => 999,
                'type' => 'timesheet',
                'timestamp' => (new \DateTimeImmutable())->format('c'),
                'data' => [
                    'action' => 'created',
                    'employee' => 'John Doe',
                    'site' => 'Test Site',
                    'client' => 'Test Client',
                    'date' => '2024-01-15',
                    'hours' => 8.0,
                    'status' => 'draft'
                ]
            ],
            'site.created' => [
                'id' => 888,
                'type' => 'site',
                'timestamp' => (new \DateTimeImmutable())->format('c'),
                'data' => [
                    'action' => 'created',
                    'name' => 'New Test Site',
                    'client' => 'Test Client',
                    'address' => '123 Test Street, Test City, 12345',
                    'status' => 'active'
                ]
            ]
        ];

        foreach ($events as $topic => $eventData) {
            $update = new Update(
                $topic,
                $this->serializer->serialize($eventData, 'json'),
                true
            );
            $this->hub->publish($update);
        }

        return $this->json([
            'message' => 'All test events published',
            'events' => array_keys($events)
        ]);
    }
}
