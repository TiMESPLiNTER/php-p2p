<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

final class PeerUri implements PeerUriInterface
{
    private string $nodeId;

    private string $address;

    private ?int $port;

    public function __construct(string $nodeId, string $address, ?int $port)
    {
        $this->nodeId = $nodeId;
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * @param string $peerUrl
     * @return static
     */
    public static function fromString(string $peerUrl): self
    {
        $matches = [];

        if (1 !== preg_match('/^([0-9a-f]{32})@([^:]+)(?:\:(\d{1,5}))?$/i', $peerUrl, $matches)) {
            throw new \InvalidArgumentException(sprintf('Peer url `%s` is not in a valid format', $peerUrl));
        }

        return new self($matches[1], $matches[2], (int) $matches[3]);
    }

    public function getNodeId(): string
    {
        return $this->nodeId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function __toString(): string
    {
        $addressStr = sprintf('%s@%s', $this->nodeId, $this->address);

        if (null !== $this->port) {
            $addressStr .= sprintf(':%d', $this->port);
        }

        return $addressStr;
    }
}
