<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple\MessageHandler;

use Psr\Log\LoggerInterface;
use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\Application\Simple\Message\SimpleMessage;
use Timesplinter\P2P\Protocol\Application\Simple\MessageWriterInterface;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

final class VersionAcknowledgedMessageHandler implements MessageHandlerInterface
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
        $this->logger->debug(sprintf('[%s] Version acknowledged', $peer->get('outboundRemoteAddress')));

        $knownNodes = [];

        $thresholdDate = new \DateTime('-3 hours');

        foreach ($node->getConnectedPeers() as $otherPeer) {
            if (null === $outboundAddress = $otherPeer->getUri()) {
                continue;
            }

            // Don't announce nodes that haven't been active for a longer time
            if ($otherPeer->getLastSeen() < $thresholdDate) {
                continue;
            }

            $knownNodes[] = (string) $outboundAddress;
        }

        $listKnownNodesMessage = new SimpleMessage(SimpleMessage::TYPE_LIST_KNOWN_NODES, $knownNodes);;

        $this->messageWriter->write($peer, $listKnownNodesMessage);
    }
}
