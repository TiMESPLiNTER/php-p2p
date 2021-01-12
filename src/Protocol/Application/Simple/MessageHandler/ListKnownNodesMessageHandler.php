<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple\MessageHandler;

use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

final class ListKnownNodesMessageHandler implements MessageHandlerInterface
{
    public function handle(NodeInterface $node, PeerInterface $peer, ProtocolMessageInterface $message): void
    {
        foreach ($message->getPayload() as $nodeAddress) {
            $node->connectToPeer($nodeAddress);
        }
    }
}
