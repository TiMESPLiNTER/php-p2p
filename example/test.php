<?php

declare(strict_types=1);

namespace Timesplinter\P2P\Message\Example;

use Ramsey\Uuid\Uuid;
use React\EventLoop\Factory;
use React\Socket\TcpConnector;
use Timesplinter\P2P\HttpNode;
use Timesplinter\P2P\ConnectionPool\ConnectionPool;
use Timesplinter\P2P\ConnectionPool\LimitedConnectionPool;
use Timesplinter\P2P\Message\SimpleMessage;
use Timesplinter\P2P\Message\SimpleMessageFactory;
use Timesplinter\P2P\MessageHandler\DelegateMessageHandler;
use Timesplinter\P2P\MessageHandler\ListKnownNodesMessageHandler;
use Timesplinter\P2P\MessageHandler\VersionAcknowledgedMessageHandler;
use Timesplinter\P2P\MessageHandler\VersionMessageHandler;
use Timesplinter\P2P\Node;
use Timesplinter\P2P\NodeEventHandler;
use Timesplinter\P2P\PeerConnector;

require __DIR__ . '/../vendor/autoload.php';

// Port this node should be reachable for other nodes
$listeningPort = (int) $argv[1] ?? 0;

// Seed peer addresses for a start
$initialPeerAddresses = isset($argv[2]) ? explode(',', $argv[2]) : [];

// Create a new connection pool to manage peer connections
$connectionPool = new LimitedConnectionPool(
    new ConnectionPool(),
    10
);

// Create a new message handle to handle messages received by connected peers
$messageHandler = new DelegateMessageHandler();

// Create message factory instance to support the basic messages required for a working node
$messageFactory = new SimpleMessageFactory();

// Handle basic events of this node (peer connected/disconnected, message received from peer, etc)
$nodeEventHandler = new NodeEventHandler($messageFactory, $messageHandler, $connectionPool);

// Create the very basic loop used for the whole reactive stuff
$loop = Factory::create();

// Create a peer connector that manages the establishment of connections to other peers discovered in the network
$peerConnector = new PeerConnector(new TcpConnector($loop), $connectionPool, $nodeEventHandler);

// Create a unique node ID (although the remote address should be unique as well)
$nodeId = Uuid::uuid4()->toString();

// Create the actual node
$node = new Node(
    $nodeId,
    $listeningPort,
    $initialPeerAddresses,
    $loop,
    $connectionPool,
    $nodeEventHandler,
    $peerConnector
);

// Add messages handlers to the delegate message handler to handle all the network messages properly
$messageHandler
    ->addMessageHandler(
        SimpleMessage::TYPE_VERSION,
        new VersionMessageHandler($nodeId, NodeEventHandler::NODE_VERSION, $connectionPool, $messageFactory)
    )
    ->addMessageHandler(
        SimpleMessage::TYPE_VERSION_ACKNOWLEDGED,
        new VersionAcknowledgedMessageHandler()
    )
    ->addMessageHandler(
        SimpleMessage::TYPE_LIST_KNOWN_NODES,
        new ListKnownNodesMessageHandler($peerConnector)
    );

// Wrap the actual node into an http node that provides a web interface with some basic stats about the wrapped node
$httpNode = new HttpNode($node, $loop, $connectionPool);

// Run the whole thing
$httpNode->run();
