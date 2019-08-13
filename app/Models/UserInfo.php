<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserInfo
 *
 * @package App\Models\User
 * @property integer $id
 * @property integer $user_id
 * @property string $first_name
 * @property string $surname
 * @property string $patronymic
 * @property string $birthday
 * @property string $country
 * @property string $city
 * @property string $description
 * @property string $activity
 * @property string $language
 * @property integer $posts
 * @property integer $followers
 * @property integer $following
 * @property string $geo
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserInfo whereUserId($value)
 */
class UserInfo extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'surname',
        'patronymic',
        'birthday',
        'country',
        'city',
        'description',
        'activity',
        'geo',
        'language',
        'posts',
        'followers',
        'following'
    ];

    /**
     * @param $object
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validation($object):\Illuminate\Validation\Validator
    {
        $rules = [
            'first_name' => 'string|max:255',
            'surname' => 'string|max:255',
            'patronymic' => 'string|max:255',
            'birthday' => 'date|max:10|date_format:d.m.Y',
            'country' => 'string|max:255',
            'city' => 'string|max:255',
            'description' => 'string',
            'activity' => 'string|max:255',
            'geo' => 'json',
            'language' => 'string|max:255',
            'posts' => 'integer',
            'followers' => 'integer',
            'following' => 'integer'
        ];

        $messages = [
            'max' => 'Превышена длина поля!',
            'regex' => 'Поле должно содержать в себе только буквы и цифры!',
            'date' => 'Поле должно содержать дату в формате d.m.Y!',
            'date_format' => 'Поле должно содержать дату в формате d.m.Y!',
            'integer' => 'Поле должно быть строго числовым!',
            'string' => 'Не верный формат поля'
        ];

        return Validator::make($object, $rules, $messages);
    }

    /**
     * Добавление информации о пользователе
     *
     * @param $data
     * @return UserInfo
     */
    public static function addInfo($data):UserInfo
    {
        return self::create($data);
    }

    /**
     * Поиск информации о пользователе или создание базовой
     *
     * @param $userId
     * @return UserInfo
     */
    public static function findByUser($userId):UserInfo
    {
        $row = self::where('user_id', $userId)->first();

        if (!$row) {
            $row = self::addInfo(['user_id' => $userId]);
        }

        return $row;
    }

    /**
     * Обновление гео данных
     *
     * @param $geo
     */
    public function updateGeo($geo):void
    {
        $this->geo = json_encode($geo);
        $this->save();
    }

    /**
     * Получение User через relations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user():HasMany
    {
        return $this->hasMany('App\Models\User\User');
    }
}
