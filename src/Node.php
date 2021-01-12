<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use Timesplinter\P2P\ConnectionPool\ConnectionBuffer;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;
use Timesplinter\P2P\Protocol\Application\ApplicationProtocolInterface;

final class Node implements NodeInterface
{
    private ServerInterface $server;

    private LoopInterface  $loop;

    private ConnectionPoolInterface $connectionPool;

    private ApplicationProtocolInterface $protocol;

    private PeerConnectorInterface $peerConnector;

    private string $nodeId;

    /**
     * @param string $nodeId
     * @param ServerInterface $server
     * @param LoopInterface $loop
     * @param ConnectionPoolInterface $connectionPool
     * @param PeerConnectorInterface $peerConnector
     * @param ApplicationProtocolInterface $protocol
     */
    public function __construct(
        string $nodeId,
        ServerInterface $server,
        LoopInterface $loop,
        ConnectionPoolInterface $connectionPool,
        PeerConnectorInterface $peerConnector,
        ApplicationProtocolInterface $protocol
    ) {
        $this->protocol = $protocol;
        $this->nodeId = $nodeId;

        $this->connectionPool = $connectionPool;

        $this->loop = $loop;
        $this->server = $server;

        $this->attachEvents($server, $loop);

        $this->peerConnector = $peerConnector;
    }

    private function attachEvents(ServerInterface $server, LoopInterface $loop): void
    {
        $server->on('connection', function (ConnectionInterface $connection) {
            $this->handleConnection($connection, null);
        });

        $loop->addTimer(0, function () use ($server) {
            parse_url($server->getAddress());

            echo sprintf("Node URI: %s\n", $this->getNodeUri());

            echo sprintf("Listening on: %s\n", $this->server->getAddress());
        });

        $server->on('error', 'printf');
    }

    public function getOutboundRemoteAddress(): string
    {
        $urlParts = parse_url($this->server->getAddress());

        return sprintf('%s:%d', $urlParts['host'], $urlParts['port']);
    }

    public function connectToPeer(string $peerUrl): void
    {
        $peerAddress = PeerUri::fromString($peerUrl);

        if ($peerAddress->getNodeId() === $this->nodeId) {
            return;
        }
        
        if (null !== $this->connectionPool->getByPeerUri($peerAddress)) {
            return;
        }

        $this->peerConnector->connectFromAddress(
            $peerAddress,
            function (ConnectionInterface $connection) use ($peerAddress) {
                $this->handleConnection($connection, $peerAddress);
            }
        );
    }

    private function handleConnection(ConnectionInterface $connection, ?PeerUriInterface $peerUri): void
    {
        $peer = new Peer($connection, $peerUri);
        $buffer = new ConnectionBuffer();

        $connection->on('data', function (string $data) use ($peer, $buffer) {
            $data = $buffer->read() . $data;

            $consumedBytes = $this->protocol->onData($this, $peer, $data);

            $buffer->write(substr($data, $consumedBytes));
        });

        $connection->on('close', function () use ($peer) {
            $this->connectionPool->remove($peer);

            $this->protocol->onPeerDisconnected($this, $peer);
        });

        $this->connectionPool->add($peer);

        $this->protocol->onPeerConnected($this, $peer);
    }

    public function disconnectFromPeer(PeerInterface $peer): void
    {
        $this->connectionPool->remove($peer);
    }

    public function getConnectedPeers(): iterable
    {
        return $this->connectionPool->getAll();
    }

    public function getConnectedPeer(string $peerAddress): PeerInterface
    {
        // TODO
    }

    public function getNodeId(): string
    {
        return $this->nodeId;
    }

    public function getNodeUri(): string
    {
        $urlParts = parse_url($this->server->getAddress());

        return sprintf('%s@%s:%d', $this->nodeId, $urlParts['host'], $urlParts['port']);
    }
}
