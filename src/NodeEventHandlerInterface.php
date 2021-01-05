<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;

interface NodeEventHandlerInterface
{
    /**
     * This peer instance is now ready to accept new peers
     * @param NodeInterface $node
     */
    public function onReady(NodeInterface $node): void;

    /**
     * A peer connected to you/you connected to a peer
     * @param ConnectionInterface $connection
     */
    public function onPeerConnected(ConnectionInterface $connection): void;

    /**
     * The connection offered by a peer has not been accepted
     * @param ConnectionInterface $connection
     * @param \Throwable $reason
     */
    public function onPeerRejected(ConnectionInterface $connection, \Throwable $reason): void;

    /**
     * A peer disconnected from you
     * @param ConnectionInterface $connection
     */
    public function onPeerDisconnected(ConnectionInterface $connection): void;

    /**
     * Received data from a peer
     * @param ConnectionInterface $connection
     * @param string $data
     */
    public function onData(ConnectionInterface $connection, string $data): void;
}
