<?php

declare(strict_types=1);


namespace Timesplinter\P2P;


interface MessageInterface
{
    public function __toString(): string;

    /**
     * @return mixed
     */
    public function getPayload();

    public function getHeaders(): array;

    public function getType(): string;
}
