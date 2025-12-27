<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpNotificationService
{
    protected $connection;
    protected $channel;
    protected $exchange;
    protected $queue;

    public function __construct()
    {
        $this->exchange = config('amqp.properties.production.exchange', 'kanban_exchange');
        $this->queue = config('amqp.properties.production.queue', 'kanban_tasks');
    }

    /**
     * Get or create AMQP connection
     */
    protected function getConnection()
    {
        if (!$this->connection || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                config('amqp.properties.production.host', 'localhost'),
                config('amqp.properties.production.port', 5672),
                config('amqp.properties.production.username', 'guest'),
                config('amqp.properties.production.password', 'guest'),
                config('amqp.properties.production.vhost', '/')
            );
        }

        return $this->connection;
    }

    /**
     * Get or create AMQP channel
     */
    protected function getChannel()
    {
        if (!$this->channel) {
            $connection = $this->getConnection();
            $this->channel = $connection->channel();

            // Declare exchange
            $this->channel->exchange_declare(
                $this->exchange,
                config('amqp.properties.production.exchange_type', 'topic'),
                false,  // passive
                true,   // durable
                false   // auto_delete
            );

            // Declare queue
            $this->channel->queue_declare(
                $this->queue,
                false,  // passive
                true,   // durable
                false,  // exclusive
                false   // auto_delete
            );

            // Bind queue to exchange
            $this->channel->queue_bind(
                $this->queue,
                $this->exchange,
                'task.*'  // routing key pattern
            );
        }

        return $this->channel;
    }

    /**
     * Send task notification to RabbitMQ
     *
     * @param mixed $task
     * @param string $action (created, updated, status_changed, deleted)
     * @return bool
     */
    public function sendTaskNotification($task, string $action): bool
    {
        try {
            // Prepare notification payload
            $notification = [
                'task_id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'action' => $action,
                'timestamp' => now()->toIso8601String(),
            ];

            // Convert to JSON
            $messageBody = json_encode($notification);

            // Create AMQP message
            $message = new AMQPMessage($messageBody, [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            // Get channel and publish
            $channel = $this->getChannel();
            $channel->basic_publish(
                $message,
                $this->exchange,
                "task.{$action}"  // routing key
            );

            Log::info('Task notification sent to RabbitMQ', [
                'task_id' => $task->id,
                'action' => $action,
                'routing_key' => "task.{$action}",
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send task notification to RabbitMQ', [
                'task_id' => $task->id ?? null,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Test RabbitMQ connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $channel = $this->getChannel();

            $testMessage = new AMQPMessage(
                json_encode([
                    'message' => 'Test connection from Laravel',
                    'timestamp' => now()->toIso8601String(),
                ]),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $channel->basic_publish($testMessage, $this->exchange, 'task.test');

            Log::info('RabbitMQ connection test successful');
            return true;

        } catch (\Exception $e) {
            Log::error('RabbitMQ connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Close connection when service is destroyed
     */
    public function __destruct()
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
            if ($this->connection && $this->connection->isConnected()) {
                $this->connection->close();
            }
        } catch (\Exception $e) {
            // Silently fail on cleanup
        }
    }
}