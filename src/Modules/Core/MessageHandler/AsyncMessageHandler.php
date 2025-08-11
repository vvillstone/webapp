<?php

namespace Modules\Core\MessageHandler;

use Modules\Core\Message\AsyncMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AsyncMessageHandler
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(AsyncMessage $message): void
    {
        $this->logger->info('Processing async message', [
            'content' => $message->getContent(),
            'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        // Simuler un traitement asynchrone
        sleep(2);

        $this->logger->info('Async message processed successfully');
    }
}
