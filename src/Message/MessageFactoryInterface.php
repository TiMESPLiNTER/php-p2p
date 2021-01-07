<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Message;

interface MessageFactoryInterface
{
    public function createVersionMessage(string $nodeId, string $nodeVersion, string $nodeAddress): MessageInterface;

    public function createVersionAcknowledgeMessage(): MessageInterface;

    public function createListKnownNodesMessage(array $knownNodes): MessageInterface;

    public function createFromString(string $messageData): MessageInterface;
}
