<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\Message\MessageInterface;

interface MessageHandlerInterface
{
    public function handle(ConnectionInterface $connection, MessageInterface $message);
}
