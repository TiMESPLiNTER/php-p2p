<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple\MessageHandler;

use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

interface MessageHandlerInterface
{
    public function handle(NodeInterface $node, PeerInterface $peer, ProtocolMessageInterface $message): void;
}
