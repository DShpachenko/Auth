<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Laravel\Lumen\Application;

/**
 * Class LoginWhiteList.
 *
 * @package App\Models\LoginWhiteList
 * @property string _id
 * @property int $user_id
 * @property string token_id
 * @property string $ip
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList create($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList where($value, $val)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginWhiteList whereUserId($value)
 */
class LoginWhiteList extends Model
{
    /**
     * Ошибочная (запретная) авторизация / обновление токена.
     */
    public const STATUS_FAILED = 0;

    /**
     * Успешная авторизация / обновление токена.
     */
    public const STATUS_SUCCESS = 1;

    /**
     * Подключение к Mongodb.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Список полей, доступных для создания / редактирования в качестве массива.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
        'token_id',
        'ip',
    ];

    /**
     * Название таблицы.
     *
     * @var string
     */
    protected $table = 'login_white_list';

    /**
     * Создание токена для нового пользователя.
     *
     * @param int $userId
     * @param string $tokenId
     * @param string $ip
     * @param int $status
     * @return LoginWhiteList|null
     * @throws \Exception
     */
    public static function add($userId, $tokenId, $ip, $status): ? LoginWhiteList
    {
        try {
            return self::create([
                'user_id' => $userId,
                'token_id' => $tokenId,
                'status' => $status,
                'ip' => $ip
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
        }

        return null;
    }

    /**
     * Проверка токена в белом списке.
     *
     * @param $tokenId
     * @param $userId
     * @param $ip
     * @return LoginWhiteList|null
     */
    public static function check($tokenId, $userId, $ip): ? LoginWhiteList
    {
        return self::where('user_id', $userId)
                   ->where('status', self::STATUS_SUCCESS)
                   ->where('token_id', $tokenId)
                   ->where('ip', $ip)
                   ->orderBy('id', 'desc')
                   ->first();
    }
}
