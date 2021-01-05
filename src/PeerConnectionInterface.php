<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

interface PeerConnectionInterface
{
    public function send(MessageInterface $message): void;
}
