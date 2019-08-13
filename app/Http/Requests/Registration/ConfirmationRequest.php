<?php

namespace App\Http\Requests\Registration;

use App\Models\User;
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса для авторизации.
 *
 * Class LoginRequest
 * @package App\Http\Requests
 */
class ConfirmationRequest extends Validation
{
    /**
     * Валидация метода регистрации.
     *
     * @param $data
     * @return bool
     */
    public function make($data)
    {
        $this->setRules([
            'phone' => 'required|string|phone|max:30',
            'code' => 'required|string|min:4',
        ]);

        $this->setMessages([
            'phone.required' => __('response.phone_required'),
            'phone.phone' => __('response.phone_phone'),
            'string' => __('response.string'),
            'max' => __('response.max'),
            'min' => __('response.min'),
            'code.required' => __('response.code_required'),
        ]);

        $this->validateForm($data);
        $this->validateUserByPhone($data['phone'], User::STATUS_NEW);

        return $this->fails();
    }
}
