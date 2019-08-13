<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserTokens
 *
 * @package App\Models\User
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property string $token
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserTokens whereUserId($value)
 */
class UserTokens extends Model
{
    /**
     * Статусы
     */
    const STATUS_NEW = 0;
    const STATUS_WORK = 1;
    const STATUS_OLD = 2;
    const STATUS_END = 3;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
        'token'
    ];

    /**
     * Список статусов токенов
     *
     * @return array
     */
    public static function getStatusList():array
    {
        return [
            self::STATUS_NEW => 'Не верефицированный',
            self::STATUS_WORK => 'Рабочий',
            self::STATUS_OLD => 'Превышено время исполнения',
            self::STATUS_END => 'Закрыта сессия'
        ];
    }

    /**
     * Создание рандомного токена
     *
     * @return string
     */
    public static function generateToken():string
    {
        return str_replace('+', '', base64_encode(time() . random_bytes(60) . rand(1, 100)));
    }

    /**
     * Создание токена для нового пользователя
     *
     * @param $userId
     * @return mixed
     */
    public static function createFirstConnection($userId):UserTokens
    {
        return self::create([
            'user_id' => $userId,
            'status' => self::STATUS_WORK,
            'token' => self::generateToken()
        ]);
    }

    /**
     * Отключение всех пользовательских токенов
     *
     * @param $userId
     */
    public static function disableUserTokens($userId):void
    {
        self::where('user_id', $userId)
            ->update(['status' => self::STATUS_END]);
    }

    /**
     * Поиск активного токена у пользователя
     *
     * @param $userId
     * @param $token
     * @return bool
     */
    public static function checkToken($userId, $token):bool
    {
        $row = self::where('user_id', $userId)
                   ->where('token', 'like', '%' . $token . '%')
                   ->where('status', self::STATUS_WORK)
                   ->first();

        if (!$row) {
            return false;
        }

        return true;
    }
}
