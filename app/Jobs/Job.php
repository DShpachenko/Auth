<?php

namespace App\Jobs;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Job
{
    /**
     * Канал.
     *
     * @var string
     */
    public $channel = '';

    /**
     * Хост для соединения с RabbitMq.
     *
     * @var string
     */
    private $host;

    /**
     * Порт для соединения с RabbitMq.
     *
     * @var string
     */
    private $port;

    /**
     * Пользователь для соединения с RabbitMq.
     *
     * @var string
     */
    private $user;

    /**
     * Пароль для соединения с RabbitMq.
     *
     * @var string
     */
    private $pass;

    /**
     * Job constructor.
     */
    public function __construct()
    {
        $this->setConfigurationToConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASS')
        );
    }

    /**
     * Подготовка данных для соединения.
     *
     * @param $host
     * @param $port
     * @param $user
     * @param $pass
     * @return void
     */
    public function setConfigurationToConnection($host, $port, $user, $pass): void
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * Установка соединения.
     * @return AMQPStreamConnection
     */
    private function connection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->pass
        );
    }

    /**
     * Отправка задачи в канал.
     *
     * @param $data
     * @return bool
     */
    public function make($data): bool
    {
        try {
            /** @var AMQPStreamConnection $connection */
            $connection = $this->connection();

            $channel = $connection->channel();
            $channel->queue_declare($this->channel, false, false, false, false);

            $msg = new AMQPMessage(json_encode($data));

            $channel->basic_publish($msg, '', $this->channel);

            $channel->close();
            $connection->close();

            return true;
        } catch (\Exception $e) {
            dd($e);
        } catch (\Throwable $t) {
            dd($t);
        }

        return false;
    }
}
