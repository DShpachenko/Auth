<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SmsCode.
 *
 * @package App\Models\Sms
 * @property integer $id
 * @property integer $user_id
 * @property string $code
 * @property integer $created_at
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
        'created_at'
    ];

    /**
     * Генерация смс кода.
     *
     * @return int
     */
    public static function generateCode(): int
    {
        return random_int(1000, 9999);
    }

    /**
     * Добавление смс кода.
     *
     * @param $userId
     * @return SmsCode|null
     */
    public static function addCode($userId): ? SmsCode
    {
        try {
            return self::create([
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
     * @return bool
     */
    public static function checkCode($userId, $code): bool
    {
        $time = time();

        $row = self::where('user_id', $userId)
                   ->where('code', $code)
                   ->orderBy('id', 'desc')
                   ->first();

        if (!$row) {
            return false;
        }

        // проверка на просрочку
        if (($time - $row->created_at) <= self::LIFE_TIME) {
            return true;
        }

        return false;
    }

    /**
     * Последнее отправленное SMS сообщение пользователю.
     *
     * @param $userId
     * @return SmsCode|null
     */
    public static function getLastByUser($userId): ? SmsCode
    {
        return self::where('user_id', $userId)
                   ->orderBy('id', 'desc')
                   ->firstOr();
    }
}
