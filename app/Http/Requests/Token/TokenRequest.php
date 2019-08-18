<?php

namespace App\Http\Requests\Token;

use App\Models\UserLogin;
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса для авторизации.
 *
 * Class TokenRequest
 * @package App\Http\Requests
 */
class TokenRequest extends Validation
{
    /**
     * Метод валидации данных для смены токена.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function make($request): bool
    {
        $data = $request->all();

        $this->setRules(['token' => 'required|min:5|max:255']);

        $this->setMessages([
            'required' => __('response.token_required'),
            'min' => __('response.min'),
            'max' => __('response.max'),
        ]);

        $this->validateForm($data);

        /** @todo убрать это условие после интеграции api с клиентов */
        if (env('APP_ENV') === 'production') {
            $this->validateIp($request->ip(), UserLogin::TYPE_TOKEN_UPDATE);
        }

        return $this->fails();
    }
}
