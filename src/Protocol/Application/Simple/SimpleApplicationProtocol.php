<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple;

use Psr\Log\LoggerInterface;
use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\Application\ApplicationProtocolInterface;
use Timesplinter\P2P\Protocol\Application\Simple\Message\SimpleMessage;
use Timesplinter\P2P\Protocol\Application\Simple\MessageHandler\MessageHandlerInterface;

final class SimpleApplicationProtocol implements ApplicationProtocolInterface
{
    public const MESSAGE_TERMINATOR = "\0";

    public const PROTOCOL_VERSION = 1;

    private MessageHandlerInterface $messageHandler;

    private MessageReaderInterface $messageReader;

    private MessageWriterInterface $messageWriter;

    private LoggerInterface $logger;

    public function __construct(
        MessageHandlerInterface $messageHandler,
        MessageReaderInterface $messageReader,
        MessageWriterInterface $messageWriter,
        LoggerInterface $logger
    ) {
        $this->messageHandler = $messageHandler;
        $this->messageReader = $messageReader;
        $this->messageWriter = $messageWriter;
        $this->logger = $logger;
    }

    public function onPeerConnected(NodeInterface $node, PeerInterface $peer): void
    {
        $this->logger->info(sprintf('[%s] New connection', $peer->getUri()));
        $this->logger->debug(
            sprintf('Total peers connected: %d', count($node->getConnectedPeers())),
            ['connected_peers' => $this->getConnectedPeersAsArray($node)]
        );

        // Announce ourselves to the newly connected peer and initiate communication
        $this->messageWriter->write($peer, new SimpleMessage(SimpleMessage::TYPE_VERSION, [
            'version' => self::PROTOCOL_VERSION,
            'node_id' => $node->getNodeId(),
            'addr_from' => $node->getOutboundRemoteAddress(),
        ]));
    }

    public function onPeerDisconnected(NodeInterface $node, PeerInterface $peer): void
    {
        $this->logger->info(sprintf('[%s] Disconnected', $peer->getUri()));
        $this->logger->debug(
            sprintf('Total peers connected: %d', count($node->getConnectedPeers())),
            ['connected_peers' => $this->getConnectedPeersAsArray($node)]
        );
    }

    public function onData(NodeInterface $node, PeerInterface $peer, string $data): int
    {
        if (false === $endPos = strpos($data, self::MESSAGE_TERMINATOR)) {
            return 0;
        }

        $messageData = substr($data, 0, $endPos);

        $message = $this->messageReader->read($messageData);

        $this->messageHandler->handle($node, $peer, $message);

        return $endPos + strlen(self::MESSAGE_TERMINATOR);
    }

    public function getName(): string
    {
        return 'simple';
    }

    public function getVersion(): int
    {
        return self::PROTOCOL_VERSION;
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
