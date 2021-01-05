<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Message;

final class SimpleMessageFactory implements MessageFactoryInterface
{

    public function createVersionMessage(string $nodeId, string $nodeVersion, string $nodeAddress): MessageInterface
    {
        return new SimpleMessage(SimpleMessage::TYPE_VERSION, [
            'version' => $nodeVersion,
            'node_id' => $nodeId,
            'addr_from' => $nodeAddress,
        ]);
    }

    public function createVersionAcknowledgeMessage(): MessageInterface
    {
        return new SimpleMessage(SimpleMessage::TYPE_VERSION_ACKNOWLEDGED);
    }

    public function createListAllKnownHostsMessage(array $knownNodes): MessageInterface
    {
        return new SimpleMessage(SimpleMessage::TYPE_LIST_KNOWN_NODES, $knownNodes);
    }

    public function createFromString(string $messageData): MessageInterface
    {
        return SimpleMessage::fromString($messageData);
    }
}
