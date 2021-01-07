<?php

declare(strict_types=1);

namespace Timesplinter\P2P\MessageHandler;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;
use Timesplinter\P2P\Message\MessageFactoryInterface;
use Timesplinter\P2P\MessageHandlerInterface;
use Timesplinter\P2P\Message\MessageInterface;

final class VersionMessageHandler implements MessageHandlerInterface
{
    private ConnectionPoolInterface $connectionPool;

    private string $nodeId;

    private string $acceptedVersion;

    private MessageFactoryInterface $messageFactory;

    private LoggerInterface $logger;

    public function __construct(
        string $nodeId,
        string $acceptedVersion,
        ConnectionPoolInterface $connectionPool,
        MessageFactoryInterface $messageFactory,
        LoggerInterface $logger
    ) {
        $this->nodeId = $nodeId;
        $this->acceptedVersion = $acceptedVersion;
        $this->connectionPool = $connectionPool;
        $this->messageFactory = $messageFactory;
        $this->logger = $logger;
    }

    public function handle(ConnectionInterface $connection, MessageInterface $message)
    {
        $nodeVersion = $message->getPayload()['version'];

        if ($this->acceptedVersion !== $nodeVersion) {
            $this->logger->error(sprintf(
                '[%s] Version `%s` is not supported',
                $connection->getRemoteAddress(),
                $nodeVersion
            ));

            // Remove connection from connection pool (connection get closed automatically by removing it)
            $this->connectionPool->remove($connection);

            return;
        }

        $nodeId = $message->getPayload()['node_id'];
        $addrFrom = $message->getPayload()['addr_from'];

        // Check if we connected to ourselves and drop connection if so
        if ($nodeId === $this->nodeId) {
            $this->logger->debug(sprintf('[%s] Loopback, dropping connection', $addrFrom));

            // Remove connection from connection pool (connection get closed automatically by removing it)
            $this->connectionPool->remove($connection);

            return;
        }

        $connectionInfo = $this->connectionPool->getInfo($connection);

        $connectionInfo->setOutboundRemoteAddress($addrFrom);
        $connectionInfo->nodeId = $message->getPayload()['node_id'];
        $connectionInfo->version = $message->getPayload()['version'];

        $versionAcknowledgeMessage = $this->messageFactory->createVersionAcknowledgeMessage();
        $this->connectionPool->sendMessage($connection, $versionAcknowledgeMessage);
    }
}
