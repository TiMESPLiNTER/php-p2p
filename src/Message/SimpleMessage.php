<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Message;

use Ramsey\Uuid\Uuid;

final class SimpleMessage implements MessageInterface
{
    public const TYPE_VERSION_ACKNOWLEDGED = 'verack';

    public const TYPE_VERSION = 'version';

    public const TYPE_LIST_KNOWN_NODES = 'addr';

    private string $type;

    /**
     * @var array<string, string>
     */
    private array $headers;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @param string $type
     * @param array<string, string> $headers
     * @param mixed $payload
     */
    public function __construct(string $type, $payload = null, array $headers = [])
    {
        $this->type = $type;
        $this->headers = $headers;
        $this->payload = $payload;
    }

    /**
     * @param string $commandStr
     * @return static
     */
    public static function fromString(string $commandStr): self
    {
        $commandParts = explode('.', $commandStr);

        if (3 !== count($commandParts)) {
            throw new \InvalidArgumentException('Not a valid command: Could not parse message data');
        }

        try {
            return new SimpleMessage(
                base64_decode($commandParts[0]),
                self::json_decode(base64_decode($commandParts[2])),
                self::json_decode(base64_decode($commandParts[1]))
            );
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Not a valid command: Could not decode message data', 0, $e);
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function __toString(): string
    {
        $headers = self::json_encode(['Reference' => Uuid::uuid4()->toString()] + $this->headers);
        $payload = self::json_encode($this->payload);

        return sprintf(
            "%s.%s.%s",
            base64_encode($this->type),
            base64_encode($headers),
            base64_encode($payload)
        );
    }

    private static function json_decode(string $data)
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    private static function json_encode($data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
