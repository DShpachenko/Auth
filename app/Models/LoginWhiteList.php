<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

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
     * Список статусов токенов.
     *
     * @return array
     */
    public static function getStatusList():array
    {
        return [
            self::STATUS_FAILED => 'Ошибочная (запретная) авторизация / обновление токена',
            self::STATUS_SUCCESS => 'Успешная авторизация / смена токена',
        ];
    }

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
     * Создание JWT токена.
     *
     * @param int $tokenId
     * @return string
     * @throws \Exception
     */
    public static function generateJwtToken($tokenId):string
    {
        // Создание заголовка (header) в формате JSON строки.
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // Создание payload.
        $payload = json_encode(['token' => $tokenId]);

        // Получение Header в формате строки вида Base64Url.
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Получение Payload в формате строки вида Base64Url.
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Создание сигнатуры.
        $signature = hash_hmac('sha256', $base64UrlHeader.".".$base64UrlPayload, self::SECRET_KEY, true);

        // Получение Signature в формате строки вида Base64Url.
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader.'.'.$base64UrlPayload.'.'.$base64UrlSignature;
    }

    /**
     * Отключение всех пользовательских токенов.
     *
     * @param $userId
     */
    public static function disableUserTokens($userId):void
    {
        self::where('user_id', $userId)
            ->update(['status' => self::STATUS_END]);
    }

    /**
     * Поиск активного токена у пользователя.
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
