<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SmsSender extends Command
{
    private const CHANNEL = 'sms_auth';

    /**
     * Название для вызова команды.
     *
     * @var string
     */
    protected $signature = 'SmsSender:run';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Обработка очереди смс сообщений';


    public function handle(): void
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASS'));
        $channel = $connection->channel();
        $channel->queue_declare('sms_auth', false, false, false, false);
        $msg = new AMQPMessage(time());
        $channel->basic_publish($msg, '', 'sms_auth');
        echo " [x] Sent ".time()."\n";
        $channel->close();
        $connection->close();
        die;
    }
}
