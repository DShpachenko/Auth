<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\Types\Mixed_;

/**
 * Class User
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
     * Типы пользователей
     */
    const TYPE_USER = 0;

    /**
     * Статусы
     */
    const STATUS_NEW = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_REPAIR_PASSWORD = 2;
    const STATUS_FROZEN = 3;
    const STATUS_DELETED = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'type',
        'status'
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
     * @param $object
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validation($object):\Illuminate\Validation\Validator
    {
        $rules = [
            'name' => 'required|string|max:255|regex: [A-Za-z0-9 ]|unique:users',
            'phone' => 'required|string|phone|max:30|unique:users',
            'password' => 'required|string|min:6',
        ];

        $messages = [
            'name.unique' => 'Логин должен быть уникальным!',
            'name.required' => 'Поле с логином должно быть заполненно!',
            'phone.unique' => 'Номер должен быть уникальным!',
            'phone.required' => 'Поле с телефоном должно быть заполненно!',
            'password.min' => 'Пароль не должен быть короче 6 символов!',
            'password.required' => 'Поле с паролем должно быть заполненно!'
        ];

        if (isset($object['phone'])) {
            $object['phone'] = self::clearPhoneNumber($object['phone']);
        }

        return Validator::make($object, $rules, $messages);
    }

    /**
     * Приведение номера к единой форме (только числа).
     *
     * @param $phone
     * @return int
     */
    public static function clearPhoneNumber($phone): int
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Список статусов (описание)
     *
     * @return array
     */
    public static function getStatusList():array
    {
        return [
            self::STATUS_NEW => 'Не подтвержденный',
            self::STATUS_VERIFIED => 'Верифицирован',
            self::STATUS_REPAIR_PASSWORD => 'Восстановление',
            self::STATUS_FROZEN => 'Заморожен',
            self::STATUS_DELETED => 'Удален'
        ];
    }

    /**
     * Регистрация пользователя
     *
     * @param $request
     * @return mixed
     */
    public static function registerUser($request):User
    {
        $name = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $request->get('name'));

        return self::create([
            'name' => strip_tags($name),
            'phone' => self::clearPhoneNumber($request->get('phone')),
            'password' => Hash::make($request->get('password')),
            'type' => self::TYPE_USER,
            'status' => self::STATUS_NEW
        ]);
    }

    /**
     * Поиск пользователя по номеру телефона
     *
     * @param $phone
     * @return mixed
     */
    public static function findByPhone($phone): ? User
    {
        return self::where('phone', self::clearPhoneNumber($phone))
                   ->orderBy('id', 'desc')
                   ->first();
    }

    /**
     * Верификация пользователя
     */
    public function confirmRegistration():void
    {
        $this->status = self::STATUS_VERIFIED;
        $this->save();
    }

    /**
     * Обновление пароля пользователя
     *
     * @param $password
     */
    public function updatePassword($password):void
    {
        $this->password = Hash::make($password);
        $this->save();
    }

    /**
     * Получение UserInfo через relations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function info():HasMany
    {
        return $this->hasMany('App\Models\User\UserInfo');
    }
}
