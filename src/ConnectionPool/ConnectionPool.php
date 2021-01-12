<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\PeerUriInterface;

final class ConnectionPool implements ConnectionPoolInterface
{
    /**
     * @var \SplObjectStorage<PeerInterface>
     */
    private \SplObjectStorage $connections;

    public function __construct()
    {
        $this->connections = new \SplObjectStorage();
    }

    public function add(PeerInterface $peer): void
    {
        $this->connections->attach($peer);
    }

    public function remove(PeerInterface $peer): void
    {
        // Ensure connection is closed before we loose any reference to it
        $peer->close();

        $this->connections->detach($peer);
    }

    public function count(): int
    {
        return $this->connections->count();
    }

    /**
     * @return iterable<PeerInterface>
     */
    public function getAll(): iterable
    {
        return $this->connections;
    }

    public function getByPeerUri(PeerUriInterface $peerUri): ?PeerInterface
    {
        foreach ($this->connections as $peer) {
            if ((string) $peer->getUri() === (string) $peerUri) {
                return $peer;
            }
        }

        return null;
    }
}
