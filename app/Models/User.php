<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

/**
 * Модель таблицы users "Пользователи".
 *
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property int $phone
 * @property string $password
 * @property int $status
 * @property int $type
 *
 * @property-read \Illuminate\Database\Eloquent\Builder|\App\Models\UserLogin $login
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @mixin \Eloquent
 */
class User extends Model
{
    /**
     * Удаленный пользователь.
     */
    public const STATUS_DELETED = -1;

    /**
     * Новый не прошедшие подтверждение пользователь.
     */
    public const STATUS_NEW = 0;

    /**
     * Прошедший подтверждение пользователь.
     */
    public const STATUS_VERIFIED = 1;

    /**
     * Замороженный пользователь.
     */
    public const STATUS_FROZEN = 3;

    /**
     * Тип - пользователь.
     */
    public const TYPE_USER = 0;

    /**
     * Тип - админ.
     */
    public const TYPE_ADMIN = 1;

    /**
     * Список правил валидации.
     */
    public const RULES = [
        'name' => 'required|string|max:255',
        'phone' => 'required|integer|max:255',
        'password' => 'string|min:6|confirmed|',
        'status' => 'required|integer',
        'type' => 'required|integer',
        'country' => 'string'
    ];

    /**
     * Список сообщеинй правил валидации.
     */
    public const RULES_MESSAGES = [
        'email.unique' => 'Пользователь с подобным Email-адресом уже существует!',
        'phone.unique' => 'Пользователь с подобным Номером уже существует!',
        'name.unique' => 'Пользователь с подобным Ником уже существует!',
        'required' => 'Отсутствует значение поля!',
        'integer' => 'Поле должно быть строго числовым!',
        'string' => 'Не верный формат поля!',
        'max' => 'Превышена длина поля!',
        'min' => 'Значение слишком короткое!',
    ];

    /**
     * Поля доступные для заполнения через массив.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * Невидимые атрибуты.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Валидирование пользовательских данных.
     *
     * @param array $data
     * @return Validator
     */
    public function validator(array $data): Validator
    {
        return Validator::make($data, self::RULES, self::RULES_MESSAGES);
    }

    public function add()
    {

    }
}
