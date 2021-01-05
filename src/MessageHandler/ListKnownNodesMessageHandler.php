<?php

declare(strict_types=1);

namespace Timesplinter\P2P\MessageHandler;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\MessageHandlerInterface;
use Timesplinter\P2P\MessageInterface;
use Timesplinter\P2P\PeerConnector;

final class ListKnownNodesMessageHandler implements MessageHandlerInterface
{
    private PeerConnector $peerConnector;

    public function __construct(PeerConnector $peerConnector)
    {
        $this->peerConnector = $peerConnector;
    }

    public function handle(ConnectionInterface $connection, MessageInterface $message)
    {
        foreach ($message->getPayload() as $nodeAddress) {
            $this->peerConnector->connectFromAddress($nodeAddress);
        }
    }
}
