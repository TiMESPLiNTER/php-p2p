<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

final class ConnectionInfo
{
    private string $buffer = '';

    private ?string $outboundRemoteAddress = null;

    public ?string $nodeId = null;

    public ?string $version = null;

    private \DateTimeInterface $lastActive;

    public function __construct()
    {
        $this->lastActive = new \DateTimeImmutable();
    }

    public function updateLastActive(): void
    {
        $this->lastActive = new \DateTimeImmutable();
    }

    public function getLastActive(): \DateTimeInterface
    {
        return $this->lastActive;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function setBuffer(string $buffer): void
    {
        $this->buffer = $buffer;
    }

    public function getOutboundRemoteAddress(): ?string
    {
        return $this->outboundRemoteAddress;
    }

    /**
     * @param string|null $outboundRemoteAddress
     */
    public function setOutboundRemoteAddress(?string $outboundRemoteAddress): void
    {
        $this->outboundRemoteAddress = $outboundRemoteAddress;
    }
}
