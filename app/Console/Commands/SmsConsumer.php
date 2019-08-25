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

        $callBack = static function ($message) {
            echo $message->body.PHP_EOL;
        };

        $rabbit->run($callBack, self::CHANNEL);

        return true;
    }
}
