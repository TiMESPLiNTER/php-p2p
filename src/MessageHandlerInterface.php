<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;

interface MessageHandlerInterface
{
    public function handle(ConnectionInterface $connection, MessageInterface $message);
}
