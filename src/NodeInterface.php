<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

interface NodeInterface
{
    /**
     * Node identification
     * @return string
     */
    public function getNodeId(): string;

    /**
     * Returns the URI of this node as a string such as `eeded407ebb74f65b18e9187cb28a06a@127.0.0.1:4711`
     * @return string
     */
    public function getNodeUri(): string;

    /**
     * Connect to another peer using the peer address represented as a string
     *
     * @param string $peerUrl
     * @return void
     */
    public function connectToPeer(string $peerUrl): void;

    /**
     * Disconnect from a peer
     *
     * @param PeerInterface $peer
     * @return void
     */
    public function disconnectFromPeer(PeerInterface $peer): void;

    /**
     * Get a list of all connected peers
     *
     * @return iterable<PeerInterface>
     */
    public function getConnectedPeers(): iterable;

    /**
     * Returns the peer for the given address
     *
     * @param string $peerAddress
     * @return PeerInterface
     */
    public function getConnectedPeer(string $peerAddress): PeerInterface;

    /**
     * Returns the address under which this node is reachable for other nodes
     * @return string
     */
    public function getOutboundRemoteAddress(): string;
}
