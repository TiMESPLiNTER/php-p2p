<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Transport;

use React\EventLoop\LoopInterface;
use React\Socket\ConnectorInterface;
use React\Socket\ServerInterface;
use React\Socket\TcpConnector;
use React\Socket\TcpServer;

final class TcpTransportProtocolFactory implements TransportProtocolFactoryInterface
{
    private LoopInterface $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function getServer(int $listeningPort): ServerInterface
    {
        return new TcpServer($listeningPort, $this->loop, [
            /*'tls' => [
                'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
            ],*/
        ]);
    }

    public function getConnector(): ConnectorInterface
    {
        return new TcpConnector($this->loop);
    }
}
