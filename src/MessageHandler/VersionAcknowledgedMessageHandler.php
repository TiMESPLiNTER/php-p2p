<?php

declare(strict_types=1);

namespace Timesplinter\P2P\MessageHandler;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\MessageHandlerInterface;
use Timesplinter\P2P\MessageInterface;

final class VersionAcknowledgedMessageHandler implements MessageHandlerInterface
{
    public function handle(ConnectionInterface $connection, MessageInterface $message)
    {
        echo sprintf("[%s] Version acknowledged\n", $connection->getRemoteAddress());
    }
}
