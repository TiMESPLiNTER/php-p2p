<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\PeerUriInterface;

interface ConnectionPoolInterface
{
    /**
     * @param PeerInterface $peer
     * @throws ConnectionPoolException
     */
    public function add(PeerInterface $peer): void;

    public function remove(PeerInterface $peer): void;

    public function count(): int;

    /**
     * @return iterable<PeerInterface>
     */
    public function getAll(): iterable;

    public function getByPeerUri(PeerUriInterface $peerUri): ?PeerInterface;
}
