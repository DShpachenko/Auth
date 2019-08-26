<?php

namespace App\Jobs;

/**
 * Class Sms.
 *
 * @package App\Jobs
 */
class Sms extends Job
{
    /**
     * Название канала очереди.
     *
     * @var string
     */
    public $channel = 'sms_auth';
}
