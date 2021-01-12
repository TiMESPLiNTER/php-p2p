<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple;

use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

interface MessageWriterInterface
{
    public function write(PeerInterface $peer, ProtocolMessageInterface $message): int;
}
