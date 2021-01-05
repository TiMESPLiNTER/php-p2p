<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;

interface PeerConnectorInterface
{
    public function connectFromAddress(string $peerAddress): void;

    public function connectFromConnection(ConnectionInterface  $connection): void;
}
