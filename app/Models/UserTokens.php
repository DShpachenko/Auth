<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class UserTokens.
 *
 * @package App\Models\UserTokens
 * @property string _id
 * @property int $user_id
 * @property int $status
 * @property string $token
 * @property int $create_time
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens where($value, $val)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTokens whereUserId($value)
 */
class UserTokens extends Model
{
    /**
     * Секретный ключ для формирвоания JWT.
     */
    private const SECRET_KEY = 'Ka3njgu3y1OF2NdsRP67IVWrY7swWgkX6M4kGJLUq4';

    /**
     * Время доступности токена как ACCESS_TOKEN всего 5 минут (60 * 5).
     */
    public const ACCESS_TIME = 300;

    /**
     * Время доступности токена как REFRESH_TOKEN всего 10 минут (60 * 10).
     */
    public const REFRESH_TIME = 600;

    /**
     * Статусы.
     */
    public const STATUS_NEW = 0;
    public const STATUS_WORK = 1;
    public const STATUS_OLD = 2;
    public const STATUS_END = 3;

    /**
     * Подключение к Mongodb.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Название таблицы.
     *
     * @var string
     */
    protected $table = 'user_tokens';

    /**
     * Список полей, доступных для создания / редактирования в качестве массива.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
        'token',
        'create_time',
    ];

    /**
     * Создание токена для нового пользователя.
     *
     * @param $userId
     * @return UserTokens|null
     * @throws \Exception
     */
    public static function add($userId): ? UserTokens
    {
        try {
            $row = self::create([
                'user_id' => $userId,
                'status' => self::STATUS_WORK,
                'create_time' => time(),
                'token' => '',
            ]);

            $row->token = self::generateJwtToken($row->_id);
            $row->save();

            return $row;
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
    public static function generateJwtToken($tokenId): string
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
    public static function disableUserTokens($userId): void
    {
        self::where('user_id', $userId)
            ->update(['status' => self::STATUS_END]);
    }
}
