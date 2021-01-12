<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

interface PeerUriInterface
{
    public function getNodeId(): string;

    public function getAddress(): string;

    public function getPort(): ?int;

    public function __toString(): string;
}
