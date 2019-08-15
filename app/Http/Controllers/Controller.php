<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Базовый контроллер.
 *
 * Class Controller
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    /**
     * Статус подтверждения успешного начала регистрации.
     */
    public const REGISTRATION_SUCCESS = 'REGISTRATION_SUCCESS';

    /**
     * Статус подтверждения успешного подтверждения регистрации.
     */
    public const CONFIRMATION_SUCCESS = 'CONFIRMATION_SUCCESS';

    /**
     * Статус подтверждения успешшного повторного отправления SMS сообщения.
     */
    public const RESEND_SMS_SUCCESS = 'RESEND_SMS_SUCCESS';

    /**
     * Статус успешного запроса на сброс пароля.
     */
    public const FORGOT_SEND_SMS_SUCCESS = 'FORGOT_SEND_SMS_SUCCESS';

    /**
     * Статус успешного сброса пароля.
     */
    public const FORGOT_CONFIRMATION_SUCCESS = 'FORGOT_CONFIRMATION_SUCCESS';

    /**
     * Статус успешного запроса на повторную отправку смс сообщения с кодом для сброса пароля.
     */
    public const FORGOT_RESEND_SMS_SUCCESS = 'FORGOT_RESEND_SMS_SUCCESS';

    /**
     * Ошибки по умолчанию.
     */
    private const DEFAULT_ERRORS = [
        'form' => null,
        'any' => null,
    ];

    /**
     * Возврат результата в формате JSON.
     *
     * @param null $data
     * @param array $errors
     * @return string
     */
    public function response($data = null, $errors = self::DEFAULT_ERRORS): string
    {
        return response()->json([
            'data' => $data,
            'errors' => $errors,
        ])->content();
    }
}
