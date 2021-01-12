<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Message\Example;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use React\EventLoop\Factory;
use Timesplinter\P2P\ConnectionPool\ConnectionPool;
use Timesplinter\P2P\ConnectionPool\LimitedConnectionPool;
use Timesplinter\P2P\Node;
use Timesplinter\P2P\PeerConnector;
use Timesplinter\P2P\Protocol\Application\Simple\Message\SimpleMessage;
use Timesplinter\P2P\Protocol\Application\Simple\MessageHandler\DelegateMessageHandler;
use Timesplinter\P2P\Protocol\Application\Simple\MessageHandler\ListKnownNodesMessageHandler;
use Timesplinter\P2P\Protocol\Application\Simple\MessageHandler\VersionAcknowledgedMessageHandler;
use Timesplinter\P2P\Protocol\Application\Simple\MessageHandler\VersionMessageHandler;
use Timesplinter\P2P\Protocol\Application\Simple\SimpleApplicationProtocol;
use Timesplinter\P2P\Protocol\Application\Simple\SimpleProtocolTransport;
use Timesplinter\P2P\Protocol\Transport\TcpTransportProtocolFactory;
use Timesplinter\P2P\WebInterface;

require __DIR__ . '/../vendor/autoload.php';

// Port this node should be reachable for other nodes
$listeningPort = (int) $argv[1] ?? 0;

// Seed peer addresses for a start
$initialPeerAddresses = isset($argv[2]) ? explode(',', $argv[2]) : [];

$loggerStdoutHandler = new StreamHandler('php://stdout', Logger::DEBUG);
$loggerStdoutHandler->setFormatter(
    new LineFormatter("[%datetime%] %level_name%: %message% %context%\n", 'Y-m-d H:i:s')
);
$logger = new Logger('default');
$logger->pushHandler($loggerStdoutHandler);

// Create a new connection pool to manage peer connections
$connectionPool = new LimitedConnectionPool(
    new ConnectionPool(),
    10
);

// Create a unique node ID (although the remote address should be unique as well)
$nodeId = Uuid::uuid4()->getHex()->toString();

$messageWriter = $messageReader = new SimpleProtocolTransport();

// Create a new message handle to handle messages received by connected peers
$messageHandler = (new DelegateMessageHandler($logger))
    ->addMessageHandler(
        SimpleMessage::TYPE_VERSION,
        new VersionMessageHandler($messageWriter, $logger)
    )
    ->addMessageHandler(
        SimpleMessage::TYPE_VERSION_ACKNOWLEDGED,
        new VersionAcknowledgedMessageHandler($messageWriter, $logger)
    )
    ->addMessageHandler(
        SimpleMessage::TYPE_LIST_KNOWN_NODES,
        new ListKnownNodesMessageHandler()
    );

$applicationProtocol = new SimpleApplicationProtocol($messageHandler, $messageReader, $messageWriter, $logger);

// Create the very basic loop used for the whole reactive stuff
$loop = Factory::create();

$transportProtocolFactory = new TcpTransportProtocolFactory($loop);

// Create a peer connector that manages the establishment of connections to other peers discovered in the network
$peerConnector = new PeerConnector($transportProtocolFactory->getConnector());

$server = $transportProtocolFactory->getServer($listeningPort);

// Create the actual node
$node = new Node(
    $nodeId,
    $server,
    $loop,
    $connectionPool,
    $peerConnector,
    $applicationProtocol
);

$loop->addTimer(0, function () use ($node, $initialPeerAddresses) {
    foreach ($initialPeerAddresses as $peerAddress) {
        $node->connectToPeer($peerAddress);
    }
});

$webInterface = new WebInterface($node, $logger);
$webInterface->createHttpServer($loop);

$loop->run();
