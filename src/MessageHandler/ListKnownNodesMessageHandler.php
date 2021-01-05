<?php

declare(strict_types=1);

namespace Timesplinter\P2P\MessageHandler;

use React\Socket\ConnectionInterface;
use Timesplinter\P2P\MessageHandlerInterface;
use Timesplinter\P2P\Message\MessageInterface;
use Timesplinter\P2P\PeerConnectorInterface;

final class ListKnownNodesMessageHandler implements MessageHandlerInterface
{
    private PeerConnectorInterface $peerConnector;

    public function __construct(PeerConnectorInterface $peerConnector)
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
