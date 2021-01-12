<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

interface PeerInterface
{
    public function write(string $data): void;

    public function getUri(): ?PeerUriInterface;

    public function setUri(PeerUriInterface $uri): void;

    public function getLastSeen(): \DateTimeInterface;

    public function updateLastSeen(): void;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void;
    
    public function close(): void;
}
