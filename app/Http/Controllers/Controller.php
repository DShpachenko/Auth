<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

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
}
