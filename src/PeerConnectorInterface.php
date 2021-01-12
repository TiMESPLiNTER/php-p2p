<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

interface PeerConnectorInterface
{
    public function connectFromAddress(PeerUriInterface $peerUrl, callable $onSuccess): void;
}
