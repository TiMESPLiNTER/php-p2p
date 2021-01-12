<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple;

use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

interface MessageReaderInterface
{
    public function read(string $data): ProtocolMessageInterface;
}
