<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

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

    public function __construct(
        MessageFactoryInterface $messageFactory,
        MessageHandlerInterface $messageHandler,
        ConnectionPoolInterface $connectionPool
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageHandler = $messageHandler;
        $this->connectionPool = $connectionPool;
    }

    public function onReady(NodeInterface $node): void
    {
        $this->node = $node;

        echo sprintf("Listening on %s\n", $node->getAddress());
    }

    public function onPeerConnected(ConnectionInterface $connection): void
    {
        echo sprintf("[%s] Connected\n", $connection->getRemoteAddress());
        echo sprintf("Total peers connected: %d\n", $this->connectionPool->count());

        $versionMessage = $this->messageFactory->createVersionMessage(
            $this->node->getNodeId(),
            self::NODE_VERSION,
            $this->node->getAddress()
        );

        $connection->write($versionMessage . NodeInterface::MESSAGE_TERMINATOR);

        $knownNodes = [];

        foreach ($this->connectionPool->getAll() as $conn) {
            if ($conn !== $connection && null !== $addrFrom = $this->connectionPool->getInfo($conn)->addrFrom) {
                $knownNodes[] = $addrFrom;
            }
        }

        $listKnownNodesMessage = $this->messageFactory->createListAllKnownHostsMessage($knownNodes);

        $connection->write($listKnownNodesMessage . NodeInterface::MESSAGE_TERMINATOR);
    }

    public function onPeerDisconnected(ConnectionInterface $connection): void
    {
        echo sprintf("[%s] Disconnected\n", $connection->getRemoteAddress());
        echo sprintf("Total peers connected: %d\n", $this->connectionPool->count());
    }

    public function onData(ConnectionInterface $connection, $data): void
    {
        echo sprintf("[%s] Data received: %s\n", $connection->getRemoteAddress(), $data);

        try {
            $message = $this->messageFactory->createFromString($data);

            $this->messageHandler->handle($connection, $message);
        } catch (\Throwable $e) {
            echo sprintf("[%s] Message data invalid: %s\n", $connection->getRemoteAddress(), $e->getMessage());
            echo $e->getTraceAsString();
        }
    }

    public function onPeerRejected(ConnectionInterface $connection, \Throwable $reason): void
    {
        echo sprintf("[%s] Connection failed: %s\n", $connection->getRemoteAddress(), $reason->getMessage());
    }
}
