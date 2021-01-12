<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

final class ConnectionBuffer
{
    private string $buffer = '';

    public function read(): string
    {
        return $this->buffer;
    }

    public function write(string $data): void
    {
        $this->buffer = $data;
    }
}
