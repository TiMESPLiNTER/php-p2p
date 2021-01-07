<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\Message\MessageInterface;

interface ConnectionPoolInterface
{
    /**
     * @param ConnectionInterface $connection
     * @throws ConnectionPoolException
     */
    public function add(ConnectionInterface $connection): void;

    public function remove(ConnectionInterface $connection): void;

    public function count(): int;

    /**
     * @return iterable<ConnectionInterface>
     */
    public function getAll(): iterable;

    public function getBuffer(ConnectionInterface $connection): string;

    public function addBuffer(ConnectionInterface $connection, string $buffer): void;

    public function getInfo(ConnectionInterface $connection): ConnectionInfo;

    public function containsPeerAddress(string $peerAddress): bool;

    public function sendMessage(ConnectionInterface $connection, MessageInterface $message);
}
