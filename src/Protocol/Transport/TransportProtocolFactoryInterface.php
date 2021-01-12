<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Transport;

use React\Socket\ConnectorInterface;
use React\Socket\ServerInterface;

interface TransportProtocolFactoryInterface
{
    public function getServer(int $listeningPort): ServerInterface;

    public function getConnector(): ConnectorInterface;
}
