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
            'name.unique' => __('response.error_uniq_nickname'),
            'name.required' => __('response.name__required'),
            'phone.unique' => __('response.phone_unique'),
            'phone.required' => __('response.phone_required'),
            'password.min' => __('response.password_min'),
            'password.required' => __('response.password_required'),
        ]);

        $this->validateForm($data);
        $this->validateIp(UserLogin::TYPE_REGISTRATION);

        return $this->fails();
    }
}
