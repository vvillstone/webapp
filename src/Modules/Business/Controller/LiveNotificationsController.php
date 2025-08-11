<?php

namespace Modules\Business\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/live-notifications')]
class LiveNotificationsController extends AbstractController
{
    public function __construct(
        private HubInterface $hub
    ) {}

    #[Route('', name: 'live_notifications_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('@Business/live_notifications/dashboard.html.twig', [
            'mercure_hub_url' => $this->getParameter('mercure.default_hub')
        ]);
    }

    #[Route('/timesheets', name: 'live_notifications_timesheets', methods: ['GET'])]
    public function timesheets(): Response
    {
        return $this->render('@Business/live_notifications/timesheets.html.twig', [
            'mercure_hub_url' => $this->getParameter('mercure.default_hub')
        ]);
    }

    #[Route('/sites', name: 'live_notifications_sites', methods: ['GET'])]
    public function sites(): Response
    {
        return $this->render('@Business/live_notifications/sites.html.twig', [
            'mercure_hub_url' => $this->getParameter('mercure.default_hub')
        ]);
    }

    #[Route('/test-event', name: 'live_notifications_test', methods: ['POST'])]
    public function testEvent(): JsonResponse
    {
        $update = new Update(
            'test.topic',
            json_encode([
                'message' => 'Test notification from server',
                'timestamp' => (new \DateTimeImmutable())->format('c'),
                'type' => 'test'
            ]),
            true
        );

        $this->hub->publish($update);

        return $this->json([
            'message' => 'Test event published successfully'
        ]);
    }

    #[Route('/test', name: 'live_notifications_test_page', methods: ['GET'])]
    public function testPage(): Response
    {
        return $this->render('@Business/live_notifications/test.html.twig', [
            'mercure_hub_url' => $this->getParameter('mercure.default_hub')
        ]);
    }
}
