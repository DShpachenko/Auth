<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

/**
 * Авторизация пользователя.
 *
 * Class LoginController
 * @package App\Http\Controllers\Api
 */
class LoginController extends Controller
{
    /**
     * Метод авторизации.
     *
     * @param LoginRequest $request
     */
    public function login(LoginRequest $request)
    {
        dd($request);
    }
}
