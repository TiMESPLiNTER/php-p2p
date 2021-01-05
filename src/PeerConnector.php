<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;

final class PeerConnector implements PeerConnectorInterface
{
    private ConnectorInterface $connector;

    private ConnectionPoolInterface $connectionPool;

    private NodeEventHandler $eventHandler;

    public function __construct(
        ConnectorInterface $connector,
        ConnectionPoolInterface $connectionPool,
        NodeEventHandler $eventHandler
    ) {
        $this->connector = $connector;
        $this->connectionPool = $connectionPool;
        $this->eventHandler = $eventHandler;
    }

    public function connectFromAddress(string $peerAddress): void
    {
        if ($this->connectionPool->containsPeerAddress($peerAddress)) {
            return;
        }

        $this->connector->connect($peerAddress)->then(
            function (ConnectionInterface $connection) {
                $this->attachEvents($connection);

                $this->connectionPool->add($connection);

                $this->eventHandler->onPeerConnected($connection);
            },
            function (\Throwable $reason) use ($peerAddress) {
                echo sprintf("[%s] Connection failed: %s\n", $peerAddress, $reason->getMessage());
            }
        );
    }

    public function connectFromConnection(ConnectionInterface $connection): void
    {
        $this->connectionPool->add($connection);

        $this->attachEvents($connection);

        $this->eventHandler->onPeerConnected($connection);
    }

    private function attachEvents(ConnectionInterface $connection): void
    {
        $connection->on('close', function() use ($connection) {
            $this->connectionPool->remove($connection);

            $this->eventHandler->onPeerDisconnected($connection);
        });

        $connection->on('data', function ($data) use ($connection) {
            $data = $this->connectionPool->getBuffer($connection) . $data;

            if (false === $packageEndPos = strpos($data, Message::MESSAGE_TERMINATOR)) {
                $this->connectionPool->addBuffer($connection, $data);
                return;
            }

            $terminatorLength = strlen(Message::MESSAGE_TERMINATOR);

            $this->connectionPool->addBuffer($connection, substr($data, $packageEndPos + $terminatorLength));

            $this->eventHandler->onData($connection, substr($data, 0, $packageEndPos));
        });
    }
}
