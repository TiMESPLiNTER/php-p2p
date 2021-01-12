<?php

declare(strict_types=1);

namespace Timesplinter\P2P\ConnectionPool;

use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\PeerUriInterface;

final class LimitedConnectionPool implements ConnectionPoolInterface
{
    private ConnectionPoolInterface $connectionPool;

    private int $maxConnections;

    /**
     * @param ConnectionPoolInterface $connectionPool
     * @param int $maxConnections
     */
    public function __construct(ConnectionPoolInterface $connectionPool, int $maxConnections)
    {
        $this->connectionPool = $connectionPool;
        $this->maxConnections = $maxConnections;
    }

    public function add(PeerInterface $peer): void
    {
        // TODO first remove old connections above a certain threshold (that might be provided over constructor)
        if ($this->connectionPool->count() >= $this->maxConnections) {
            throw new ConnectionPoolException(sprintf('Maximum connections reached: %d', $this->maxConnections));
        }

        $this->connectionPool->add($peer);
    }

    public function remove(PeerInterface $peer): void
    {
        $this->connectionPool->remove($peer);
    }

    public function count(): int
    {
        return $this->connectionPool->count();
    }

    /**
     * @return iterable<PeerInterface>
     */
    public function getAll(): iterable
    {
        return $this->connectionPool->getAll();
    }

    public function getByPeerUri(PeerUriInterface $peerUri): ?PeerInterface
    {
        return $this->connectionPool->getByPeerUri($peerUri);
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }
}
