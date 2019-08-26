<?php

namespace App\Jobs;

/**
 * Class UserInfo.
 *
 * @package App\Jobs
 */
class UserInfo extends Job
{
    /**
     * Название канала очереди.
     *
     * @var string
     */
    public $channel = 'user_info_add';
}
