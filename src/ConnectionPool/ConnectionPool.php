<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

use React\Socket\ConnectionInterface;

final class ConnectionPool implements ConnectionPoolInterface
{
    /**
     * @var \SplObjectStorage<ConnectionInterface, ConnectionInfo>
     */
    private \SplObjectStorage $connections;

    public function __construct()
    {
        $this->connections = new \SplObjectStorage();
    }

    public function add(ConnectionInterface $connection): void
    {
        $this->connections->attach($connection, new ConnectionInfo());
    }

    public function remove(ConnectionInterface $connection): void
    {
        // Ensure connection is closed before we loose any reference to it
        $connection->close();

        $this->connections->detach($connection);
    }

    public function count(): int
    {
        return $this->connections->count();
    }

    public function getAll(): iterable
    {
        return $this->connections;
    }

    public function getBuffer(ConnectionInterface $connection): string
    {
        $connectionInfo = $this->connections->offsetGet($connection);

        $buffer = $connectionInfo->buffer;

        $connectionInfo->buffer = '';

        return $buffer;
    }

    public function addBuffer(ConnectionInterface $connection, string $buffer): void
    {
        $connectionInfo = $this->connections->offsetGet($connection);

        $connectionInfo->buffer .= $buffer;
    }

    public function getInfo(ConnectionInterface $connection): ConnectionInfo
    {
        return $this->connections->offsetGet($connection);
    }

    public function containsPeerAddress(string $peerAddress): bool
    {
        foreach ($this->connections as $connection) {
            $info = $this->getInfo($connection);

            if ($info->addrFrom === $peerAddress) {
                return true;
            }
        }

        return false;
    }
}
