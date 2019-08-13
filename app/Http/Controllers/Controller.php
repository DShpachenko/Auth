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
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($data = null, $errors = self::DEFAULT_ERRORS): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * Ответ сервера при критической ошибке.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function criticalResponse()
    {
        return response()->json([
            'data' => null,
            'errors' => [__('response.error_critical')],
        ]);
    }
}
