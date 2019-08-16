<?php

namespace App\Http\Requests\Registration;

use App\Models\User;
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
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function make($request): bool
    {
        $data = $request->all();

        if (isset($data['phone']) && $data['phone'] !== '') {
            $data['phone'] = User::clearPhoneNumber($data['phone']);
        }

        $this->setRules([
            'name' => 'required|string|max:50|regex:/(^([a-zA-Z]+)(\d+)?$)/u|unique:users',
            'phone' => 'required|max:30|unique:users',
            'password' => 'required|string|min:6|max:50',
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

        /** @todo убрать это условие после интеграции api с клиентов */
        if (env('APP_ENV') !== 'production') {
            $this->validateIp($request->ip(), UserLogin::TYPE_REGISTRATION);
        }

        return $this->fails();
    }
}
