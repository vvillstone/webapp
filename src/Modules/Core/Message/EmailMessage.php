<?php

namespace Modules\Core\Message;

class EmailMessage
{
    public function __construct(
        private string $to,
        private string $subject,
        private string $body,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
