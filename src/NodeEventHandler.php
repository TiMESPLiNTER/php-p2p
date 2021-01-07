<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;
use Timesplinter\P2P\Message\MessageFactoryInterface;

final class NodeEventHandler implements NodeEventHandlerInterface
{
    public const NODE_VERSION = 'php-p2p/1.0';

    private MessageHandlerInterface $messageHandler;

    private ConnectionPoolInterface $connectionPool;

    private ?NodeInterface $node;

    private MessageFactoryInterface $messageFactory;

    private LoggerInterface $logger;

    public function __construct(
        MessageFactoryInterface $messageFactory,
        MessageHandlerInterface $messageHandler,
        ConnectionPoolInterface $connectionPool,
        LoggerInterface $logger
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageHandler = $messageHandler;
        $this->connectionPool = $connectionPool;
        $this->logger = $logger;
    }

    public function onReady(NodeInterface $node): void
    {
        $this->node = $node;

        $this->logger->info(sprintf("Listening on %s", $node->getAddress()));
    }

    public function onPeerConnected(ConnectionInterface $connection): void
    {
        $this->logger->info(sprintf('[%s] Connected', $connection->getRemoteAddress()));
        $this->logger->debug(sprintf('Total peers connected: %d', $this->connectionPool->count()));

        $versionMessage = $this->messageFactory->createVersionMessage(
            $this->node->getNodeId(),
            self::NODE_VERSION,
            $this->node->getAddress()
        );

        $this->connectionPool->sendMessage($connection, $versionMessage);

        $knownNodes = [];

        $thresholdDate = new \DateTime('-3 hours');

        foreach ($this->connectionPool->getAll() as $conn) {
            if (
                null === ($info = $this->connectionPool->getInfo($conn))->getOutboundRemoteAddress()
            ) {
                continue;
            }

            // Don't announce nodes that haven't been active for a longer time
            if ($info->getLastActive() < $thresholdDate) {
                continue;
            }

            $knownNodes[] = $info->getOutboundRemoteAddress();
        }

        $listKnownNodesMessage = $this->messageFactory->createListKnownNodesMessage($knownNodes);

        $this->connectionPool->sendMessage($connection, $listKnownNodesMessage);
    }

    public function onPeerDisconnected(ConnectionInterface $connection): void
    {
        $this->logger->info(sprintf('[%s] Disconnected', $connection->getRemoteAddress()));
        $this->logger->debug(sprintf('Total peers connected: %d', $this->connectionPool->count()));
    }

    public function onData(ConnectionInterface $connection, $data): void
    {
        $this->logger->debug(
            sprintf('[%s] Message received', $connection->getRemoteAddress()),
            [
                'addr_from' => $connection->getRemoteAddress(),
                'raw_message_data' => $data,
            ]
        );

        try {
            $message = $this->messageFactory->createFromString($data);

            $this->messageHandler->handle($connection, $message);
        } catch (\Throwable $e) {
            $this->logger->notice(sprintf(
                '[%s] Message data invalid: %s',
                $connection->getRemoteAddress(),
                $e->getMessage()
            ));
        }
    }

    public function onPeerRejected(ConnectionInterface $connection, \Throwable $reason): void
    {
        $this->logger->notice(sprintf(
            '[%s] Connection failed: %s',
            $connection->getRemoteAddress(),
            $reason->getMessage()
        ));
    }
}
