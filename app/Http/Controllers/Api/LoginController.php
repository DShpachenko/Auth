<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\ {User, UserTokens, LoginWhiteList};
use App\Http\Requests\Login\LoginRequest;

/**
 * Авторизация пользователя и получение токенов (access_token, refresh_token).
 *
 * Class LoginController
 * @package App\Http\Controllers\Api
 */
class LoginController extends Controller
{
    /**
     * Авторизация.
     *
     * @param Request $request
     * @return string
     */
    public function login(Request $request):string
    {
        try {
            $validator = new LoginRequest();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();

            if (!Hash::check($request->get('password'), $user->password)) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.failed_login_pass')));
            }

            $token = UserTokens::add($user->id);

            if (!$token) {
                LoginWhiteList::add($user->id, $token->_id, $request->ip(), LoginWhiteList::STATUS_FAILED);

                return $this->response(null, $validator->getErrorsByMessage(__('response.failed_login_pass')));
            }

            LoginWhiteList::add($user->id, $token->_id, $request->ip(), LoginWhiteList::STATUS_SUCCESS);

            return $this->response([
                'status' => self::LOGIN_SUCCESS,
                'token' => $token->token,
                'token_created_time' => $token->create_time,
                'access_time' => UserTokens::ACCESS_TIME,
                'refresh_time' => UserTokens::REFRESH_TIME,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }
}
