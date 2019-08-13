<?php

namespace App\Http\Requests\Registration;

use App\Models\UserLogin;
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса для авторизации.
 *
 * Class LoginRequest
 * @package App\Http\Requests
 */
class RegistrationRequest extends Validation
{
    /**
     * Метод валидации регистрационных данных.
     *
     * @param $data
     * @return bool
     */
    public function make($data)
    {
        $this->setRules([
            'name' => 'required|string|max:255|regex: [A-Za-z0-9 ]|unique:users',
            'phone' => 'required|string|phone|max:30|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $this->setMessages([
            'name.unique' => 'Логин должен быть уникальным!',
            'name.required' => 'Поле с логином должно быть заполненно!',
            'phone.unique' => 'Номер должен быть уникальным!',
            'phone.required' => 'Поле с телефоном должно быть заполненно!',
            'password.min' => 'Пароль не должен быть короче 6 символов!',
            'password.required' => 'Поле с паролем должно быть заполненно!'
        ]);

        $this->validateForm($data);
        $this->validateIp(UserLogin::TYPE_REGISTRATION);

        return $this->fails();
    }
}
