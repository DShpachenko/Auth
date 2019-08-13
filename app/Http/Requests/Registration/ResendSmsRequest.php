<?php

namespace App\Http\Requests\Registration;

use App\Models\{User, UserLogin};
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса повторной отправки sms сообщения.
 *
 * Class ResendSmsRequest
 * @package App\Http\Requests
 */
class ResendSmsRequest extends Validation
{
    /**
     * Валидация метода повторной отправки sms сообщения.
     *
     * @param $data
     * @return bool
     */
    public function make($data)
    {
        $this->setRules(['phone' => 'required|string|phone|max:30']);

        $this->setMessages([
            'phone.required' => __('response.phone_required'),
            'phone.phone' => __('response.phone_phone'),
            'string' => __('response.string'),
            'max' => __('response.max'),
            'min' => __('response.min'),
        ]);

        $this->validateForm($data);
        $this->validateIp(UserLogin::TYPE_RESENDING_SMS);
        $this->validateUserByPhone($data['phone'], User::STATUS_NEW);

        return $this->fails();
    }
}
