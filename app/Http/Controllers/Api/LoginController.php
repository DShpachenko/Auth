<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Services\GeoIPApi;
use App\Models\ {User, UserTokens, UserLogin};
use App\Http\Requests\Login\LoginRequest;

class LoginController extends Controller
{
    /**
     * Авторизация
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

            $token = UserTokens::createFirstConnection($user->id);

            try {
                $geo = (new GeoIPApi())->getInfo($request->ip());

                dd($geo);
            } catch (\Exception $e) {
                \Log::error($e);
            }

            return $this->response(['status' => self::REGISTRATION_SUCCESS, 'token' => $token->token]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }
}
