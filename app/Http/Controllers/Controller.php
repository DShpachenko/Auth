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

    /**
     * Ответ сервера при критической ошибке.
     *
     * @return string
     */
    public function criticalResponse(): string
    {
        return response()->json([
            'data' => null,
            'errors' => [__('response.error_critical')],
        ])->content();
    }
}
