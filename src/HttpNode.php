<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Timesplinter\P2P\ConnectionPool\ConnectionPoolInterface;

/**
 * Decorates a node with a web interface that displays some basic information about the decorated node
 */
final class HttpNode implements NodeInterface
{
    private NodeInterface $node;

    private LoopInterface $loop;

    private ConnectionPoolInterface $connectionPool;

    public function __construct(NodeInterface $node, LoopInterface $loop, ConnectionPoolInterface $connectionPool)
    {
        $this->node = $node;
        $this->loop = $loop;
        $this->connectionPool = $connectionPool;
    }

    public function run(): void
    {
        $this->createHttpServer($this->loop);

        $this->node->run();
    }

    public function getAddress(): string
    {
        return $this->node->getAddress();
    }

    public function getNodeId(): string
    {
        return $this->node->getNodeId();
    }

    private function createHttpServer(LoopInterface $loop): HttpServer
    {
        $server = new HttpServer(function (ServerRequestInterface $request) use (&$counter) {
            $peerList = [];

            foreach ($this->connectionPool->getAll() as $connection) {
                $info = $this->connectionPool->getInfo($connection);
                $peerList[] = sprintf(
                    ' - %s (version: %s, address: %s)',
                    $info->nodeId,
                    $info->version,
                    $info->addrFrom
                );
            }

            $body = sprintf(
                "Node ID: %s\nAddress: %s\nConnected peers: %d\n\n%s",
                $this->node->getNodeId(),
                $this->node->getAddress(),
                $this->connectionPool->count(),
                implode("\n", $peerList)
            );

            return new Response(
                200,
                array(
                    'Content-Type' => 'text/plain'
                ),
                $body
            );
        });

        $socket = new SocketServer('0.0.0.0:0', $loop);
        $server->listen($socket);

        echo 'Web interface reachable under ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

        return $server;
    }
}
