<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Protocol\Application\Simple;

use Ramsey\Uuid\Uuid;
use Timesplinter\P2P\PeerInterface;
use Timesplinter\P2P\Protocol\Application\Simple\Message\SimpleMessage;
use Timesplinter\P2P\Protocol\ProtocolMessageInterface;

final class SimpleProtocolTransport implements MessageReaderInterface, MessageWriterInterface
{
    public const MESSAGE_TERMINATOR = "\0";

    public function read(string $data): ProtocolMessageInterface
    {
        $commandParts = explode('.', $data);

        if (3 !== count($commandParts)) {
            throw new \InvalidArgumentException('Not a valid message: Could not parse message data => ' . $data);
        }

        $baseHeaders = ['Content-Length' => strlen($data) + strlen(self::MESSAGE_TERMINATOR)];

        try {
            return new SimpleMessage(
                base64_decode($commandParts[0]),
                self::json_decode(base64_decode($commandParts[2])),
                $baseHeaders + self::json_decode(base64_decode($commandParts[1]))
            );
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Not a valid message: Could not decode message data', 0, $e);
        }
    }

    public function write(PeerInterface $peer, ProtocolMessageInterface $message): int
    {
        $messageData = $this->serializeMessage($message) . self::MESSAGE_TERMINATOR;

        $peer->write($messageData);

        return strlen($messageData);
    }

    private function serializeMessage(ProtocolMessageInterface $message): string
    {
        try {
            $headers = self::json_encode(['Reference' => Uuid::uuid4()->toString()] + $message->getHeaders());
            $payload = self::json_encode($message->getPayload());

            return sprintf(
                "%s.%s.%s",
                base64_encode($message->getType()),
                base64_encode($headers),
                base64_encode($payload)
            );
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Not a valid message: Could not encode message', 0, $e);
        }
    }

    /**
     * @param string $data
     * @return mixed
     * @throws \JsonException
     */
    private static function json_decode(string $data)
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $data
     * @return string
     * @throws \JsonException
     */
    private static function json_encode($data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
