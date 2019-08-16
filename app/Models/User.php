<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Class User.
 *
 * @package App\Models\User
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property string $password
 * @property integer $status
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 */
class User extends Model
{
    /**
     * Типы пользователей.
     */
    public const TYPE_USER = 0;

    /**
     * Статусы.
     */
    public const STATUS_NEW = 0;
    public const STATUS_VERIFIED = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'status',
        'type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Приведение номера к единой форме (только числа).
     *
     * @param $phone
     * @return int
     */
    public static function clearPhoneNumber($phone): int
    {
        return preg_replace('/[\D]/', '', $phone);
    }

    /**
     * Регистрация пользователя.
     *
     * @param array $data
     * @return User|null
     */
    public static function registerUser($data): ? User
    {
        try {
            $name = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data['name']);

            return self::create([
                'name' => strip_tags($name),
                'phone' => self::clearPhoneNumber($data['phone']),
                'password' => Hash::make($data['password']),
                'type' => self::TYPE_USER,
                'status' => self::STATUS_NEW
            ]);
        } catch(\Exception $e) {
            \Log::error($e);
        } catch(\Throwable $t) {
            \Log::error($t);
        }

        return null;
    }

    /**
     * Поиск пользователя по номеру телефона.
     *
     * @param $phone
     * @return User|null
     */
    public static function findByPhone($phone): ? User
    {
        return self::where('phone', self::clearPhoneNumber($phone))
                   ->orderBy('id', 'desc')
                   ->first();
    }

    /**
     * Верификация пользователя.
     *
     * @return bool
     */
    public function confirmRegistration(): bool
    {
        try {
            $this->status = self::STATUS_VERIFIED;
            $this->save();

            return true;
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return false;
    }

    /**
     * Обновление пароля пользователя.
     *
     * @param $password
     * @return bool
     */
    public function updatePassword($password): bool
    {
        try {
            $this->password = Hash::make($password);
            $this->save();

            return true;
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return false;
    }
}
