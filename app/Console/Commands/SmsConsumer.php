<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\RabbitMQ;

class SmsConsumer extends Command
{
    private const CHANNEL = 'sms_auth';

    /**
     * Название для вызова команды.
     *
     * @var string
     */
    protected $signature = 'SmsConsumer:run';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Обработка очереди смс сообщений';

    /**
     * Выполнение команды.
     *
     * @return mixed
     */
    public function handle()
    {
        $rabbit = new RabbitMQ();

        $callBack = static function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            //sleep(substr_count($msg->body, '.'));
        };

        $rabbit->run($callBack, self::CHANNEL);

        return true;
    }
}
