<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Services\GeoIPApi;
use App\Models\Images;
use App\Models\ {User, UserTokens, UserLogin};

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
        if (!UserLogin::checkIpAccess($request->ip(), UserLogin::TYPE_LOGIN)) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Превышено число попыток, попробуйте позже'
                ]
            ]);
        }

        $user = User::findByPhone($request->get('phone'));

        if (!$user || $user->status !== User::STATUS_VERIFIED) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Не верный логин / пароль'
                ]
            ]);
        }

        if (!Hash::check($request->get('password'), $user->password)) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Не верный логин / пароль'
                ]
            ]);
        }

        $token = UserTokens::createFirstConnection($user->id);

        try {
            //$geo = (new GeoIPApi())->getInfo('213.138.95.197', 'ru');
            //$user->info()->update([
            //    'geo' => json_encode($geo),
            //    'city' => $geo['city'],
            //    'country' => $geo['country'],
            //    'language' => $geo['iso_code']
            //]);
        } catch (\Exception $e) {
            \Log::error($e);
        }

        return $this->response([
            'status' => self::STATUS_SUCCESS,
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'avatar' => Images::getAvatar($user->id, true),
                'token' => $token->token,
                'info' => $user->info()->first()
            ]
        ]);
    }
}
