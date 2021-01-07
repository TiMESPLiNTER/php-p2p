<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\Message\MessageInterface;

final class LimitedConnectionPool implements ConnectionPoolInterface
{
    private ConnectionPoolInterface $connectionPool;

    private int $maxConnections;

    /**
     * @param ConnectionPoolInterface $connectionPool
     * @param int $maxConnections
     */
    public function __construct(ConnectionPoolInterface $connectionPool, int $maxConnections)
    {
        $this->connectionPool = $connectionPool;
        $this->maxConnections = $maxConnections;
    }

    public function add(ConnectionInterface $connection): void
    {
        if ($this->connectionPool->count() >= $this->maxConnections) {
            throw new ConnectionPoolException(sprintf('Maximum connections reached: %d', $this->maxConnections));
        }

        $this->connectionPool->add($connection);
    }

    public function remove(ConnectionInterface $connection): void
    {
        $this->connectionPool->remove($connection);
    }

    public function count(): int
    {
        return $this->connectionPool->count();
    }

    public function getAll(): iterable
    {
        return $this->connectionPool->getAll();
    }

    public function getBuffer(ConnectionInterface $connection): string
    {
        return $this->connectionPool->getBuffer($connection);
    }

    public function addBuffer(ConnectionInterface $connection, string $buffer): void
    {
        $this->connectionPool->addBuffer($connection, $buffer);
    }

    public function getInfo(ConnectionInterface $connection): ConnectionInfo
    {
        return $this->connectionPool->getInfo($connection);
    }

    public function containsPeerAddress(string $peerAddress): bool
    {
        return $this->connectionPool->containsPeerAddress($peerAddress);
    }

    public function sendMessage(ConnectionInterface $connection, MessageInterface $message)
    {
        $this->connectionPool->sendMessage($connection, $message);
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }
}
