<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserLogin
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin create($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin where($value, $val)
 * @mixin \Eloquent
 * @property int $id
 * @property string $ip
 * @property int $user_id
 * @property int $type
 * @property int $status
 * @property int $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin whereUserId($value)
 */
class UserLogin extends Model
{
    /**
     * Допустимый лимит запросов определенного типа за день.
     */
    public const LIMIT_DAY_ACTIONS = 20;

    /**
     * Лимит обновлений токена за день.
     */
    public const LIMIT_TOKEN_UPDATES_FOR_DAY = 160;

    /**
     * Допустимый лимит запросов определенного типа за промежуток времени.
     */
    public const LIMIT_ACTIONS = 5;

    /**
     * Допустимый лимит времени (5 минут) для определенного типа запросов.
     */
    public const PERIOD_FOR_ACTIONS = 300;

    /**
     * Статусы операции.
     */
    public const STATUS_DISALLOW = 0;
    public const STATUS_ALLOW = 1;

    /**
     * Типы операций.
     */
    public const TYPE_REGISTRATION = 0;
    public const TYPE_LOGIN = 1;
    public const TYPE_RESENDING_SMS = 2;
    public const TYPE_REPEAT_PASSWORD = 3;
    public const TYPE_TOKEN_UPDATE = 4;

    /**
     * Отключение авто дат.
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
     * Проверка дневного лиммита совершенных операций.
     *
     * @param $ip
     * @param $type
     * @param $status
     * @return bool
     */
    private static function checkDayLimitActions($ip, $type, $status): bool
    {
        $from = strtotime(date("Y-m-d 00:00:00"));
        $to = strtotime(date("Y-m-d 23:59:59"));

        $count = self::where('ip', $ip)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->where('status', $status)
            ->where('type', $type)
            ->count();

        $limit = $type === self::TYPE_TOKEN_UPDATE ? self::LIMIT_TOKEN_UPDATES_FOR_DAY : self::LIMIT_DAY_ACTIONS;

        if ($count >= $limit) {
            return false;
        }

        return true;
    }

    /**
     * Проверка (доступа) превышения количества обращений с одного IP адреса.
     *
     * @param $ip
     * @param $type
     * @return bool
     */
    public static function checkIpAccess($ip, $type): bool
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
     * Добавление опирации по IP адресу.
     *
     * @param $ip
     * @param $type
     * @param $status
     * @param null $userId
     * @return UserLogin|null
     */
    public static function addAction($ip, $type, $status, $userId = null): ? UserLogin
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
