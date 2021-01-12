<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple\MessageHandler;

use Psr\Log\LoggerInterface;
use Timesplinter\P2P\NodeInterface;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

final class DelegateMessageHandler implements MessageHandlerInterface
{
    /**
     * @var array<string, MessageHandlerInterface>
     */
    private array $messageHandlerMap = [];

    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $messageType
     * @param MessageHandlerInterface $messageHandler
     * @return static
     */
    public function addMessageHandler(string $messageType, MessageHandlerInterface $messageHandler): self
    {
        $this->messageHandlerMap[$messageType] = $messageHandler;

        return $this;
    }

    public function handle(NodeInterface $node, PeerInterface $peer, ProtocolMessageInterface $message): void
    {
        $messageType = $message->getType();

        if (false === array_key_exists($messageType, $this->messageHandlerMap)) {
            $this->logger->notice(sprintf(
                '[%s] Unsupported message type: %s',
                $peer->get('outboundRemoteAddress'),
                $messageType
            ));
            return;
        }

        $this->logger->debug(sprintf(
            '[%s] Message of type `%s` received',
            $peer->get('outboundRemoteAddress'),
            $messageType
        ));

        $messageHandler = $this->messageHandlerMap[$messageType];

        $messageHandler->handle($node, $peer, $message);
    }
}
