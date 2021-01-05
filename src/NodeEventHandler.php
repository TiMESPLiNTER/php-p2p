<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;

final class NodeEventHandler implements NodeEventHandlerInterface
{
    public const NODE_VERSION = 'php-p2p/1.0';

    private MessageHandlerInterface $messageHandler;

    private ConnectionPoolInterface $connectionPool;

    private ?NodeInterface $node;

    public function __construct(MessageHandlerInterface $messageHandler, ConnectionPoolInterface $connectionPool)
    {
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

        $versionMessage = new Message(Message::TYPE_VERSION, [
            'version' => self::NODE_VERSION,
            'node_id' => $this->node->getNodeId(),
            'addr_from' => $this->node->getAddress(),
        ]);

        $connection->write((string) $versionMessage);

        $payload = [];

        foreach ($this->connectionPool->getAll() as $conn) {
            if ($conn !== $connection && null !== $addrFrom = $this->connectionPool->getInfo($conn)->addrFrom) {
                $payload[] = $addrFrom;
            }
        }

        $listKnownNodes = new Message(Message::TYPE_LIST_KNOWN_NODES, $payload);

        $connection->write((string) $listKnownNodes);
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
            $message = Message::fromString($data);

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
