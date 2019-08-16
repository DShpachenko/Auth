<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SmsCode.
 *
 * @package App\Models\Sms
 * @property int $id
 * @property int $status
 * @property int $type
 * @property int $user_id
 * @property int $code
 * @property int $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\SmsCode whereUserId($value)
 */
class SmsCode extends Model
{
    /**
     * Время жизни (актуальности) смс кода.
     */
    public const LIFE_TIME = 300;

    /**
     * Количество секунд до повторной отправки SMS сообщения.
     */
    public const SECONDS_BEFORE_NEXT = 50;

    /**
     * Тип регистрация.
     */
    public const TYPE_REGISTRATION = 0;

    /**
     * Тип повторная отправка смс при регистрации.
     */
    public const TYPE_REGISTRATION_RESEND = 1;

    /**
     * Тип восстановление пароля.
     */
    public const TYPE_PASSWORD_RECOVERY = 2;

    /**
     * Тип повторная отправка смс сообщения при запросе на восстановление пароля.
     */
    public const TYPE_PASSWORD_RECOVERY_RESEND = 3;

    /**
     * Статус новые, не использованный.
     */
    public const STATUS_NEW = 0;

    /**
     * Статус использованный.
     */
    public const STATUS_USED = 1;

    /**
     * Отключение авто дат.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms_code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'code',
        'created_at',
        'status',
        'type',
    ];

    /**
     * Генерация смс кода.
     *
     * @return int
     * @throws \Exception
     */
    public static function generateCode(): int
    {
        return random_int(1000, 9999);
    }

    /**
     * Добавление смс кода.
     *
     * @param int $userId
     * @param int $type
     * @return SmsCode|null
     */
    public static function addCode($userId, $type = self::TYPE_REGISTRATION): ? SmsCode
    {
        try {
            return self::create([
                'status' => self::STATUS_NEW,
                'type' => $type,
                'user_id' => $userId,
                'code' => self::generateCode(),
                'created_at' => time()
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return null;
    }

    /**
     * Проверка на валидность смс кода.
     *
     * @param $userId
     * @param $code
     * @param array $types
     * @return bool
     */
    public static function checkCode($userId, $code, $types): bool
    {
        $time = time();

        $row = self::where('user_id', $userId)
                   ->where('code', $code)
                   ->where('status', self::STATUS_NEW)
                   ->whereIn('type', $types)
                   ->orderBy('id', 'desc')
                   ->first();

        if (!$row) {
            return false;
        }

        // проверка на просрочку
        if (($time - $row->created_at) <= self::LIFE_TIME) {
            $row->status = self::STATUS_USED;
            $row->save();

            return true;
        }

        return false;
    }

    /**
     * Последнее отправленное SMS сообщение пользователю.
     *
     * @param int $userId
     * @param array $type
     * @return SmsCode|null
     */
    public static function getLastByUser($userId, $type): ? SmsCode
    {
        return self::where('user_id', $userId)
                   ->whereIn('type', $type)
                   ->orderBy('id', 'desc')
                   ->first();
    }
}
