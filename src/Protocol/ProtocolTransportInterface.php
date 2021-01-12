<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol;

interface ProtocolTransportInterface
{
    public function readMessage(string $data): int;

    public function writeMessage(ProtocolMessageInterface $message): int;

    public function close(): void;
}
