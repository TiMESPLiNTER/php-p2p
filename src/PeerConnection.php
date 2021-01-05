<?php

declare(strict_types=1);

namespace Timesplinter\P2P;

use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

final class PeerConnection implements ConnectionInterface
{
    private bool $awaitingResponse = false;

    private array $messageStack;

    private ConnectionInterface $connection;

    private MessageHandlerInterface $messageHandler;

    public function __construct(ConnectionInterface $connection, MessageHandlerInterface  $messageHandler)
    {
        $this->connection = $connection;
        $this->messageHandler = $messageHandler;
        $this->messageStack = [];

        $this->attachEvents($connection);
    }

    public function write($data)
    {
        $this->messageStack[] = $data;

        $this->writeNextMessageIfNotBlocked();
    }

    public function getRemoteAddress(): ?string
    {
        return $this->connection->getRemoteAddress();
    }

    public function getLocalAddress(): ?string
    {
        return $this->connection->getLocalAddress();
    }

    public function on($event, callable $listener): void
    {
        $this->connection->on($event, $listener);
    }

    public function once($event, callable $listener): void
    {
        $this->connection->once($event, $listener);
    }

    public function removeListener($event, callable $listener): void
    {
        $this->connection->removeListener($event, $listener);
    }

    public function removeAllListeners($event = null): void
    {
        $this->connection->removeAllListeners($event);
    }

    public function listeners($event = null)
    {
        $this->connection->listeners($event);
    }

    public function emit($event, array $arguments = []): void
    {
        $this->connection->emit($event, $arguments);
    }

    public function isReadable()
    {
        return $this->connection->isReadable();
    }

    public function pause(): void
    {
        $this->connection->pause();
    }

    public function resume(): void
    {
        $this->connection->resume();
    }

    public function pipe(WritableStreamInterface $dest, array $options = []): WritableStreamInterface
    {
        return $this->connection->pipe($dest, $options);
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function isWritable(): bool
    {
        return $this->connection->isWritable();
    }

    public function end($data = null): void
    {
        $this->connection->end($data);
    }

    private function attachEvents(ConnectionInterface $connection): void
    {
        $connection->on('data', function ($data) use ($connection) {
            echo sprintf("Received from %s: %s\n", $connection->getRemoteAddress(), $data);

            $this->awaitingResponse = false;
            $this->writeNextMessageIfNotBlocked();

            try {
                $message = Message::fromString($data);

                $this->messageHandler->handle($connection, $message);
            } catch (\Throwable $e) {
                echo sprintf("Command data invalid: %s\n", $e->getMessage());
            }
        });
    }

    private function writeNextMessageIfNotBlocked(): void
    {
        if (true === $this->awaitingResponse) {
            return;
        }

        if (null !== $message = array_shift($this->messageStack)) {
            $this->connection->write((string) $message);
            $this->awaitingResponse = true;
        }
    }
}
