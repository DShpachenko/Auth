<?php

namespace App\Http\Requests\Registration;

use App\Models\User;
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса для подтверждения регистрации.
 *
 * Class ConfirmationRequest
 * @package App\Http\Requests
 */
class ConfirmationRequest extends Validation
{
    /**
     * Валидация метода подтверждения регистрации.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function make($request): bool
    {
        $this->setRules([
            'phone' => 'required|max:30',
            'code' => 'required|min:4|max:10',
        ]);

        $this->setMessages([
            'phone.required' => __('response.phone_required'),
            'phone.phone' => __('response.phone_phone'),
            'string' => __('response.string'),
            'max' => __('response.max'),
            'min' => __('response.min'),
            'code.required' => __('response.code_required'),
        ]);

        $this->validateForm($request->all());
        $this->validateUserByPhone($request->get('phone'), User::STATUS_NEW);

        return $this->fails();
    }
}
