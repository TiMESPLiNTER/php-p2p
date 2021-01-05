<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Socket\TcpServer;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolException;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;

final class Node implements NodeInterface
{
    private string $nodeId;

    private ServerInterface $server;

    private LoopInterface  $loop;

    private PeerConnectorInterface $peerConnector;

    private NodeEventHandlerInterface $eventHandler;

    private ConnectionPoolInterface $connectionPool;

    private int $listeningPort;

    /**
     * @var array<string>
     */
    private array $initialPeerAddresses;

    /**
     * @param string $nodeId
     * @param int $listeningPort
     * @param array<string> $initialPeerAddresses
     * @param LoopInterface $loop
     * @param ConnectionPoolInterface $connectionPool
     * @param NodeEventHandlerInterface $eventHandler
     * @param PeerConnectorInterface $peerConnector
     */
    public function __construct(
        string $nodeId,
        int $listeningPort,
        array $initialPeerAddresses,
        LoopInterface $loop,
        ConnectionPoolInterface $connectionPool,
        NodeEventHandlerInterface $eventHandler,
        PeerConnectorInterface $peerConnector
    ) {
        $this->nodeId = $nodeId;
        $this->listeningPort = $listeningPort;
        $this->initialPeerAddresses = $initialPeerAddresses;

        $this->connectionPool = $connectionPool;
        $this->eventHandler = $eventHandler;

        $this->loop = $loop;
        $this->server = $this->getServer($this->loop);
        $this->peerConnector = $peerConnector;
    }

    public function run(): void
    {
        $this->initPeerConnections($this->loop, $this->initialPeerAddresses);

        $this->eventHandler->onReady($this);

        $this->loop->run();
    }

    private function getServer(LoopInterface $loop): ServerInterface
    {
        $server = new TcpServer($this->listeningPort, $loop, [
            /*'tls' => [
                'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
            ],*/
        ]);

        $server->on('connection', function (ConnectionInterface $connection) {
            try {
                $this->peerConnector->connectFromConnection($connection);
            } catch (ConnectionPoolException $e) {
                $this->eventHandler->onPeerRejected($connection, $e);
            }
        });

        $server->on('error', 'printf');

        return $server;
    }

    private function initPeerConnections(LoopInterface $loop, array $peerAddresses): void
    {
        foreach ($peerAddresses as $peerAddress) {
            $this->peerConnector->connectFromAddress($peerAddress);
        }
    }

    public function getAddress(): string
    {
        return $this->server->getAddress();
    }

    public function getNodeId(): string
    {
        return $this->nodeId;
    }
}
