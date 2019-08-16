<?php

namespace App\Http\Requests\Forgot;

use App\Models\User;
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса для подтверждения регистрации.
 *
 * Class ConfirmationRequest
 * @package App\Http\Requests
 */
class ConfirmationForgotRequest extends Validation
{
    /**
     * Валидация метода подтверждения регистрации.
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
            'code' => 'required|min:4|max:10',
            'phone' => 'required|min:5|max:30',
            'password' => 'required|min:6|max:50',
        ]);

        $this->setMessages([
            'code.required' => __('response.code_required'),
            'phone.required' => __('response.phone_required'),
            'password.required' => __('response.password_required'),
            'min' => __('response.min'),
            'max' => __('response.max'),
        ]);

        $this->validateForm($data);
        $this->validateUserByPhone($data['phone'], User::STATUS_VERIFIED);

        return $this->fails();
    }
}
