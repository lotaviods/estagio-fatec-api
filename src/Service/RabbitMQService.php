<?php

namespace App\Service;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    public function __construct(string $dsn)
    {
        $dsnParts = parse_url($dsn);
        $host = $dsnParts['host'];
        $port = $dsnParts['port'];
        $username = $dsnParts['user'];
        $password = $dsnParts['pass'] ?? null;

        $this->connection = new AMQPStreamConnection($host, $port, $username, $password);
        $this->channel = $this->connection->channel();
    }

    public function publish(string $payload, string $channel): void
    {
        $this->channel->queue_declare($channel, false, true, false, false);

        $this->channel->basic_publish(new AMQPMessage($payload), '', $channel);
    }
}
