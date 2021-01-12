<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application;

use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;

interface ApplicationProtocolInterface
{
    public function getName(): string;

    public function getVersion(): int;

    public function onPeerConnected(NodeInterface $node, PeerInterface $peer): void;

    public function onPeerDisconnected(NodeInterface $node, PeerInterface $peer): void;

    /**
     * @param NodeInterface $node
     * @param PeerInterface $peer
     * @param string $data
     * @return int Consumed bytes
     */
    public function onData(NodeInterface $node, PeerInterface $peer, string $data): int;
}
