<?php

declare(strict_types=1);

namespace Timesplinter\P2P\MessageHandler;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Timesplinter\P2P\Message\MessageInterface;
use Timesplinter\P2P\MessageHandlerInterface;

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

    public function handle(ConnectionInterface $connection, MessageInterface $message)
    {
        $messageType = $message->getType();

        if (false === array_key_exists($messageType, $this->messageHandlerMap)) {
            $this->logger->notice(sprintf(
                '[%s] Unsupported message type: %s',
                $connection->getRemoteAddress(),
                $messageType
            ));
            return;
        }

        $this->logger->debug(sprintf(
            '[%s] Message of type `%s` received',
            $connection->getRemoteAddress(),
            $messageType
        ));

        $messageHandler = $this->messageHandlerMap[$messageType];

        $messageHandler->handle($connection, $message);
    }
}
