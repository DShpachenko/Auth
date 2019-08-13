<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Sms
 *
 * @package App\Models\Sms
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property integer $type
 * @property string $text
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sms\Sms whereUserId($value)
 */
class Sms extends Model
{
    /**
     * Лимит отправки сообщений за день для пользователя
     */
    const DAY_LIMIT_SENDING = 20;

    /**
     * Типы
     */
    const TYPE_REGISTRATION = 0;
    const TYPE_PASSWORD_RECOVERY = 1;

    /**
     * Статусы
     */
    const STATUS_NEW = 0;
    const STATUS_IN_WORK = 1;
    const STATUS_FINISHED = 2;
    const STATUS_FAILED = 3;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
        'type',
        'text'
    ];

    /**
     * Список статусов (описание)
     *
     * @return array
     */
    public static function getStatusList():array
    {
        return [
            self::STATUS_NEW => 'Новое',
            self::STATUS_IN_WORK => 'В работе',
            self::STATUS_FINISHED => 'Успешно',
            self::STATUS_FAILED => 'Ошибка'
        ];
    }

    /**
     * Список типов (описание)
     *
     * @return array
     */
    public static function getTypeList():array
    {
        return [
            self::TYPE_REGISTRATION => 'Регистрация',
            self::TYPE_PASSWORD_RECOVERY => 'Восстановление пароля'
        ];
    }

    /**
     * Добавление смс для рассылки
     *
     * @param $userId
     * @param $type
     * @param $text
     * @return mixed
     */
    public static function addSms($userId, $type, $text):Sms
    {
        // @todo добавить очередь отправки смс сообщения

        return self::create([
            'user_id' => $userId,
            'status' => self::STATUS_NEW,
            'type' => $type,
            'text' => $text
        ]);
    }

    /**
     * Проверка отправки смс сообщения для восстановления
     *
     * @param $userId
     * @return bool
     */
    public static function checkRepairSms($userId, $type):bool
    {
        $row = self::where('user_id', $userId)
                   // @todo расскоментировать когда будут готовы механизмы очередей
                   //->where('status', self::STATUS_FINISHED)
                   ->where('type', $type)
                   ->orderBy('id', 'desc')
                   ->first();

        if (!$row) {
            return false;
        }

        return true;
    }
}
