<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;

final class Peer implements PeerInterface
{
    private ConnectionInterface $connection;

    private ?PeerUriInterface $uri;

    private \DateTimeInterface $lastSeen;

    private array $info = [];

    public function __construct(ConnectionInterface $connection, ?PeerUriInterface $uri)
    {
        $this->connection = $connection;
        $this->uri = $uri;
        $this->lastSeen = new \DateTimeImmutable();
    }

    public function getUri(): ?PeerUriInterface
    {
        return $this->uri;
    }

    public function setUri(PeerUriInterface $uri): void
    {
        if (null !== $this->uri) {
            throw new \RuntimeException('A peers URI cannot be changed');
        }

        $this->uri = $uri;
    }

    public function write(string $data): void
    {
        $this->connection->write($data);
    }

    public function getLastSeen(): \DateTimeInterface
    {
        return $this->lastSeen;
    }

    public function updateLastSeen(): void
    {
        $this->lastSeen = new \DateTimeImmutable();
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function get(string $key)
    {
        return $this->info[$key] ?? null;
    }

    public function set(string $key, $value): void
    {
        $this->info[$key] = $value;
    }
}
