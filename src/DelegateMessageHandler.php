<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;

final class DelegateMessageHandler implements MessageHandlerInterface
{
    /**
     * @var array<string, MessageHandlerInterface>
     */
    private array $messageHandlerMap;

    /**
     * @param array<string, MessageHandlerInterface> $messageHandlerMap
     */
    public function __construct(array $messageHandlerMap = [])
    {
        $this->messageHandlerMap = $messageHandlerMap;
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
            echo sprintf("[%s] Invalid message type: %s.\n", $connection->getRemoteAddress(), $messageType);
            return;
        }

        echo sprintf("[%s] Message of type `%s` received\n", $connection->getRemoteAddress(), $messageType);

        $messageHandler = $this->messageHandlerMap[$messageType];

        $messageHandler->handle($connection, $message);
    }
}
