<?php

namespace Modules\Notification\Message;

class NotificationMessage
{
    public function __construct(
        private string $title,
        private string $message,
        private string $type = 'info',
        private ?string $recipient = null,
        private array $metadata = []
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
