<?php

namespace Modules\Api\Controller;

use Modules\Core\Message\AsyncMessage;
use Modules\Core\Message\EmailMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/test')]
#[IsGranted('ROLE_USER')]
class ApiTestController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private HubInterface $hub
    ) {
    }

    #[Route('/async', name: 'api_test_async', methods: ['POST'])]
    public function testAsync(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? 'Test async message';

        $message = new AsyncMessage($content);
        $this->messageBus->dispatch($message);

        return $this->json([
            'message' => 'Async message dispatched successfully',
            'content' => $content
        ]);
    }

    #[Route('/email', name: 'api_test_email', methods: ['POST'])]
    public function testEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $emailMessage = new EmailMessage(
            $data['to'] ?? 'test@example.com',
            $data['subject'] ?? 'Test Email',
            $data['body'] ?? 'This is a test email from Symfony Messenger'
        );

        $this->messageBus->dispatch($emailMessage);

        return $this->json([
            'message' => 'Email message dispatched successfully',
            'to' => $emailMessage->getTo(),
            'subject' => $emailMessage->getSubject()
        ]);
    }

    #[Route('/mercure', name: 'api_test_mercure', methods: ['POST'])]
    public function testMercure(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? 'Test Mercure update';

        $update = new Update(
            'https://example.com/notifications',
            json_encode([
                'message' => $content,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ])
        );

        $this->hub->publish($update);

        return $this->json([
            'message' => 'Mercure update published successfully',
            'content' => $content
        ]);
    }

    #[Route('/status', name: 'api_test_status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            'user' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'anonymous'
        ]);
    }
}
