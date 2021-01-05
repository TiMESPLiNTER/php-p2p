<?php

declare(strict_types=1);

namespace Timesplinter\P2P\MessageHandler;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;
use Timesplinter\P2P\Message;
use Timesplinter\P2P\MessageHandlerInterface;
use Timesplinter\P2P\MessageInterface;

final class VersionMessageHandler implements MessageHandlerInterface
{
    private ConnectionPoolInterface $connectionPool;

    private string $nodeId;

    private string $acceptedVersion;

    public function __construct(string $nodeId, string $acceptedVersion, ConnectionPoolInterface $connectionPool)
    {
        $this->nodeId = $nodeId;
        $this->acceptedVersion = $acceptedVersion;
        $this->connectionPool = $connectionPool;
    }

    public function handle(ConnectionInterface $connection, MessageInterface $message)
    {
        $nodeVersion = $message->getPayload()['version'];

        if ($this->acceptedVersion !== $nodeVersion) {
            echo sprintf("[%s] Version `%s` is not supported\n", $connection->getRemoteAddress(), $nodeVersion);

            // Remove connection from connection pool (connection get closed automatically by removing it)
            $this->connectionPool->remove($connection);

            return;
        }

        $nodeId = $message->getPayload()['node_id'];

        if ($nodeId === $this->nodeId) {
            echo sprintf("[%s] It's me\n", $connection->getRemoteAddress());

            // Remove connection from connection pool (connection get closed automatically by removing it)
            $this->connectionPool->remove($connection);

            return;
        }

        $connectionInfo = $this->connectionPool->getInfo($connection);

        $connectionInfo->addrFrom = $message->getPayload()['addr_from'];
        $connectionInfo->nodeId = $message->getPayload()['node_id'];
        $connectionInfo->version = $message->getPayload()['version'];

        $versionAcknowledgeMessage = new Message(Message::TYPE_VERSION_ACKNOWLEDGED);
        $connection->write((string) $versionAcknowledgeMessage);
    }
}
