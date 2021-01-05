<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

interface NodeInterface
{
    public const MESSAGE_TERMINATOR = "\0";

    public function run(): void;

    public function getAddress(): string;

    public function getNodeId(): string;
}
