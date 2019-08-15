<?php

namespace App\Http\Requests\Forgot;

use App\Models\{User, UserLogin};
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса сброса пароля.
 *
 * Class ForgotRequest
 * @package App\Http\Requests
 */
class ForgotRequest extends Validation
{
    /**
     * Валидация метода запроса сброса пароля.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function make($request): bool
    {
        $this->setRules([
            'phone' => 'required|min:5|max:30',
        ]);

        $this->setMessages([
            'phone.required' => __('response.phone_required'),
            'max' => __('response.max'),
            'min' => __('response.min'),
        ]);

        $this->validateForm($request->all());

        /** @todo убрать это условие после интеграции api с клиентов */
        if (env('APP_ENV') !== 'production') {
            $this->validateIp($request->ip(), UserLogin::TYPE_REPEAT_PASSWORD);
        }

        $this->validateUserByPhone($request->get('phone'), User::STATUS_VERIFIED);

        return $this->fails();
    }
}
