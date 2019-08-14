<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {User, UserTokens, UserLogin};
use App\Models\ {Sms, SmsCode};
use App\Http\Requests\Forgot\{ForgotRequest};

/**
 * Запрос, подтверждение и повторная отправка смс с кодом для восстановления пароля.
 *
 * Class ForgotController
 * @package App\Http\Controllers\Api
 */
class ForgotController extends Controller
{
    /**
     * Запрос на сброс пароля (отправляется смс код для подтверждения нового пароля).
     *
     * @param Request $request
     * @return string
     */
    public function forgot(Request $request):string
    {
        if (!UserLogin::checkIpAccess($request->ip(), UserLogin::TYPE_REPEAT_PASSWORD)) {
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
                    'message' => 'Пользователь не найден или не верифицирован'
                ]
            ]);
        }

        $code = SmsCode::addCode($user->id);

        Sms::addSms($user->id, Sms::TYPE_PASSWORD_RECOVERY, $code->code);

        return $this->response(['status' => self::STATUS_SUCCESS]);
    }

    /**
     * Подтверждение сброса пароля (установка нового пароля)
     *
     * @param Request $request
     * @return string
     */
    public function confirmation(Request $request):string
    {
        $user = User::findByPhone($request->get('phone'));

        if (!$user || $user->status !== User::STATUS_VERIFIED) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Пользователь не найден или не верифицирован'
                ]
            ]);
        }

        if (!Sms::checkRepairSms($user->id, Sms::TYPE_PASSWORD_RECOVERY)) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Не верный код (возможно уже не актуальный) или сообщение не отправлено'
                ]
            ]);
        }

        if (!SmsCode::checkCode($user->id, $request->get('code'))) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Не верный код (возможно уже не актуальный) или сообщение не отправлено'
                ]
            ]);
        }

        $user->updatePassword($request->get('password'));

        UserTokens::disableUserTokens($user->id);

        return $this->response(['status' => self::STATUS_SUCCESS]);
    }

    /**
     * Повторная отправка смс для подтверждения сброса пароля
     *
     * @param Request $request
     * @return string
     */
    public function resendingSms(Request $request):string
    {
        if (!UserLogin::checkIpAccess($request->ip(), UserLogin::TYPE_RESENDING_SMS)) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Пользователь не найден или не верифицирован'
                ]
            ]);
        }

        $user = User::findByPhone($request->get('phone'));

        if (!$user || $user->status !== User::STATUS_VERIFIED) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Пользователь не найден или не верифицирован'
                ]
            ]);
        }

        $code = SmsCode::addCode($user->id);

        Sms::addSms($user->id, Sms::TYPE_PASSWORD_RECOVERY, $code->code);

        return $this->response(['status' => self::STATUS_SUCCESS]);
    }
}
