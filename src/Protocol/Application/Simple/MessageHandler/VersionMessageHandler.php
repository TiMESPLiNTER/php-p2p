<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple\MessageHandler;

use Psr\Log\LoggerInterface;
use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\PeerUri;
use Timesplinter\P2P\Protocol\Application\Simple\Message\SimpleMessage;
use Timesplinter\P2P\Protocol\Application\Simple\MessageWriterInterface;
use Timesplinter\P2P\Protocol\Application\Simple\SimpleApplicationProtocol;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

final class VersionMessageHandler implements MessageHandlerInterface
{
    private MessageWriterInterface $messageWriter;

    private LoggerInterface $logger;

    public function __construct(MessageWriterInterface $messageWriter, LoggerInterface $logger)
    {
        $this->messageWriter = $messageWriter;
        $this->logger = $logger;
    }

    public function handle(NodeInterface $node, PeerInterface $peer, ProtocolMessageInterface $message): void
    {
        $nodeVersion = $message->getPayload()['version'];

        if (SimpleApplicationProtocol::PROTOCOL_VERSION !== $nodeVersion) {
            $this->logger->error(sprintf(
                '[%s] Version `%s` is not supported',
                $peer->get('outboundRemoteAddress'),
                $nodeVersion
            ));

            // Remove connection from connection pool (connection get closed automatically by removing it)
            $node->disconnectFromPeer($peer);

            return;
        }

        $nodeId = $message->getPayload()['node_id'];
        $addrFrom = $message->getPayload()['addr_from'];

        // Check if we connected to ourselves and drop connection if so
        if ($nodeId === $node->getNodeId()) {
            $this->logger->debug(sprintf('[%s] Loopback, dropping connection', $addrFrom));
            $this->logger->debug(
                sprintf('Total peers connected: %s', count($node->getConnectedPeers())),
                ['connected_peers' => $this->getConnectedPeersAsArray($node)]
            );

            // Remove connection from connection pool (connection get closed automatically by removing it)
            $node->disconnectFromPeer($peer);

            return;
        }

        if (null === $peer->getUri()) {
            $peer->setUri(PeerUri::fromString(sprintf('%s@%s', $nodeId, $addrFrom)));
        }

        $peer->set('version', $message->getPayload()['version']);

        $this->messageWriter->write($peer, new SimpleMessage(SimpleMessage::TYPE_VERSION_ACKNOWLEDGED));
    }

    private function getConnectedPeersAsArray(NodeInterface $node): array
    {
        $peers = [];

        foreach ($node->getConnectedPeers() as $peer) {
            $peers[] = (string) $peer->getUri();
        }

        return $peers;
    }
}
