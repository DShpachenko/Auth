<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User\UserLogin
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $ip
 * @property int $user_id
 * @property int $type
 * @property int $status
 * @property int $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLogin whereUserId($value)
 */
class UserLogin extends Model
{
    // Допустимый лимит запросов определенного типа за день
    const LIMIT_DAY_ACTIONS = 20;

    // Допустимый лимит запросов определенного типа за промежуток времени
    const LIMIT_ACTIONS = 5;

    // Допустимый лимит времени (5 минут) для определенного типа запросов
    const PERIOD_FOR_ACTIONS = 300;

    // Период блокировки (5 минут)
    const BAN_PERIOD = 300;

    /**
     * Статусы операции
     */
    const STATUS_DISALLOW = 0;
    const STATUS_ALLOW = 1;

    /**
     * Типы операций
     */
    const TYPE_REGISTRATION = 0;
    const TYPE_LOGIN = 1;
    const TYPE_RESENDING_SMS = 2;
    const TYPE_REPEAT_PASSWORD = 3;

    /**
     * Отключение авто дат
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'user_id',
        'type',
        'status',
        'created_at'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_login';

    /**
     * Проверка дневного лиммита совершенных операций
     *
     * @param $ip
     * @param $type
     * @return bool
     */
    private static function checkDayLimitActions($ip, $type, $status):bool
    {
        $from = strtotime(date("Y-m-d 00:00:00"));
        $to = strtotime(date("Y-m-d 23:59:59"));

        $count = self::where('ip', $ip)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->where('status', $status)
            ->where('type', $type)
            ->count();

        if ($count >= self::LIMIT_DAY_ACTIONS) {
            return false;
        }

        return true;
    }

    /**
     * Проверка (доступа) превышения количества обращений с одного IP адреса
     *
     * @param $ip
     * @param $type
     * @return bool
     */
    public static function checkIpAccess($ip, $type):bool
    {
        $status = self::STATUS_ALLOW;

        if ($type === self::TYPE_LOGIN) {
            $status = self::STATUS_DISALLOW;
        }

        if (!self::checkDayLimitActions($ip, $type, $status)) {
            self::addAction($ip, $type, self::STATUS_DISALLOW);

            return false;
        }

        $oldTime = time() - self::PERIOD_FOR_ACTIONS;

        $count = self::where('ip', $ip)
                     ->where('created_at', '>=', $oldTime)
                     ->where('status', $status)
                     ->where('type', $type)
                     ->count();

        if ($count >= self::LIMIT_ACTIONS) {
            self::addAction($ip, $type, self::STATUS_DISALLOW);

            return false;
        }

        self::addAction($ip, $type, self::STATUS_ALLOW);

        return true;
    }

    /**
     * Добавление опирации по IP адресу
     *
     * @param $ip
     * @param $type
     * @param $status
     * @param null $userId
     * @return UserLogin
     */
    public static function addAction($ip, $type, $status, $userId = null):UserLogin
    {
        return self::create([
            'ip' => $ip,
            'user_id' => $userId,
            'type' => $type,
            'status' => $status,
            'created_at' => time()
        ]);
    }
}
