<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

final class ConnectionInfo
{
    public string $buffer = '';

    public ?string $addrFrom = null;

    public ?string $nodeId = null;

    public ?string $version = null;
}
