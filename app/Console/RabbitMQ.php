<?php

namespace App\Console;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQ
{
    /**
     * Количество сообщений для обработки.
     *
     * @var int
     */
    public const MAX_MESSAGES_TO_HANDLE = 10;

    /**
     * Время жизни соединения (в секундах).
     */
    public const CONNECTION_LIFE_TIME = 3;

    /**
     * Максимальное количество попыток восстановления соединения.
     */
    public const MAX_CONNECTION_ATTEMPTS = 30;

    /**
     * Максимальное количество сообщений для разовой итерации.
     *
     * @var int
     */
    private $maxMessagesToHandle;

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
     * Время жизни соединения.
     *
     * @var int
     */
    private $connectionLifeTime;

    /**
     * RabbitMQ constructor.
     */
    public function __construct()
    {
        $this->setMaxMessagesToHandle(self::MAX_MESSAGES_TO_HANDLE);
        $this->setConnectionLifeTime(self::CONNECTION_LIFE_TIME);
        $this->setConfigurationToConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASS')
        );
    }

    /**
     * Утановка лимита сообщений за итерацию.
     *
     * @param int $maxMessagesToHandle
     * @return void
     */
    public function setMaxMessagesToHandle($maxMessagesToHandle): void
    {
        $this->maxMessagesToHandle = $maxMessagesToHandle;
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
     * Время жизни скрипта.
     *
     * @param $seconds
     * @return void
     */
    public function setConnectionLifeTime($seconds): void
    {
        $this->connectionLifeTime = $seconds;
    }

    /**
     * Запуск обработки очереди.
     *
     * @param $callbackFunction
     * @param $queue
     * @return void
     */
    public function run($callbackFunction, $queue): void
    {
        try {
            $end = time() + $this->connectionLifeTime;

            $connectionAttempts = 0;

            while (time() < $end && $connectionAttempts < self::MAX_CONNECTION_ATTEMPTS) {
                $connectionAttempts++;

                try {
                    $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);

                    try {
                        $channel = $connection->channel();
                        $channel->basic_qos(null, $this->maxMessagesToHandle, null);
                        $channel->queue_declare($queue, false, false, false, false);
                        $channel->basic_consume($queue, '', false, false, false, false, $callbackFunction);

                        while ($channel->is_consuming()) {
                            $channel->wait();
                        }

                        $connectionAttempts = 0;
                    } catch (\Exception $e) {
                        dd('не удалось прочитать очередь', $e);
                    }
                } catch (\Exception $e) {
                    dd('не удалось установить соединение', $e);
                }
            }
        } catch (\Exception $e) {
            dd('exception', $e);
        } catch (\Throwable $t) {
            dd('throwable', $t);
        }
    }
}
