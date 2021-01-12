<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;

/**
 * Decorates a node with a web interface that displays some basic information about the decorated node
 */
final class WebInterface
{
    private NodeInterface $node;

    private LoggerInterface $logger;

    public function __construct(NodeInterface $node, LoggerInterface $logger) {
        $this->node = $node;
        $this->logger = $logger;
    }

    public function createHttpServer(LoopInterface $loop): HttpServer
    {
        $server = new HttpServer(function (ServerRequestInterface $request) use (&$counter) {
            $peerList = [];

            $connectedPeers = $this->node->getConnectedPeers();

            foreach ($connectedPeers as $connection) {
                $peerList[] = sprintf(
                    ' - %s (last active: %s)',
                    $connection->getUri(),
                    $connection->getLastSeen()->format(\DateTimeInterface::ATOM)
                );
            }

            $peerCount = (string) count($connectedPeers);

            $body = sprintf(
                "Node ID: %s\nAddress: %s\nConnected peers: %s\n\n%s",
                $this->node->getNodeId(),
                $this->node->getOutboundRemoteAddress(),
                $peerCount,
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

        $loop->addTimer(0, function () use ($socket) {
            $this->logger->info(sprintf(
                'Web interface reachable under %s',
                str_replace('tcp:', 'http:', $socket->getAddress())
            ));
        });

        return $server;
    }
}
