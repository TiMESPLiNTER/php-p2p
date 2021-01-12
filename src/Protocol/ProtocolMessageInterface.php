<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol;

interface ProtocolMessageInterface
{
    /**
     * @return mixed
     */
    public function getPayload();

    public function getHeaders(): array;

    public function getType(): string;
}
