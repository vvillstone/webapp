<?php

namespace Modules\Core\Message;

class AsyncMessage
{
    public function __construct(
        private string $content,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
