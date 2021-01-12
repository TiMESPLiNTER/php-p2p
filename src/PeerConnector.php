<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectorInterface;

final class PeerConnector implements PeerConnectorInterface
{
    private ConnectorInterface $connector;

    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function connectFromAddress(PeerUriInterface $peerUrl, callable $onSuccess): void
    {
        $this->connector->connect($peerUrl)->then(
            $onSuccess,
            function (\Throwable $reason) use ($peerUrl) {
                echo sprintf("[%s] Connection failed: %s\n", $peerUrl, $reason->getMessage());
            }
        );
    }
}
