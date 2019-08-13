<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {User, UserLogin, UserInfo};
use App\Models\ {Sms, SmsCode};
use App\Http\Requests\Registration\RegistrationRequest;
use App\Http\Requests\Registration\ConfirmationRequest;

/**
 * Регистрация, подтверждение регистрации, повторная отправка смс для верификации
 *
 * Class RegisterController
 * @package App\Http\Controllers\Api\Auth
 */
class RegisterController extends Controller
{
    /**
     * Регистрация пользователя
     *
     * @param Request $request
     * @return string
     */
    public function registration(Request $request): string
    {
        $validator = new RegistrationRequest();

        if ($validator->make($request->all())) {
            return $this->response(null, $validator->getErrors());
        }

        $user = User::registerUser($request);
        $code = SmsCode::addCode($user->id);

        Sms::addSms($user->id, Sms::TYPE_REGISTRATION, $code->code);

        return $this->response(['status' => self::REGISTRATION_SUCCESS]);
    }

    /**
     * Подтверждение регистрации
     *
     * @param Request $request
     * @return string
     */
    public function confirmation(Request $request): string
    {
        $user = User::findByPhone($request->get('phone'));

        if (!$user || $user->status !== User::STATUS_NEW) {
            return $this->response([
                'errors' => [
                    'message' => 'Пользователь не найден или не находится на стадии верификации'
                ]
            ]);
        }

        if (!Sms::checkRepairSms($user->id, Sms::TYPE_REGISTRATION)) {
            return $this->response([
                'errors' => [
                    'message' => 'Не верный код (возможно уже не актуальный) или сообщение не отправлено'
                ]
            ]);
        }

        if (!SmsCode::checkCode($user->id, $request->get('code'))) {
            return $this->response([
                'errors' => [
                    'message' => 'Не верный код (возможно уже не актуальный) или сообщение не отправлено'
                ]
            ]);
        }

        $user->confirmRegistration();
        UserInfo::findByUser($user->id);

        return $this->response(['status' => self::STATUS_SUCCESS]);
    }

    /**
     * Повторная отправка смс для подтверждения регистрации
     *
     * @param Request $request
     * @return string
     */
    public function resendingSms(Request $request): string
    {
        $user = User::findByPhone($request->get('phone'));
        $lastCode = SmsCode::getLastByUser($user->id);

        if ((time() - $lastCode->created_at) < SmsCode::SECONDS_BEFORE_NEXT) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Подождите 1 минуту после отправки предыдущего сообщения'
                ]
            ]);
        }

        if (!UserLogin::checkIpAccess($request->ip(), UserLogin::TYPE_RESENDING_SMS)) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Превышено число попыток, попробуйте снова через 5 минут'
                ]
            ]);
        }

        if (!$user || $user->status !== User::STATUS_NEW) {
            return $this->response([
                'status' => self::STATUS_FAILED,
                'errors' => [
                    'message' => 'Пользователь не найден или не находится на стадии верификации'
                ]
            ]);
        }

        $code = SmsCode::addCode($user->id);

        Sms::addSms($user->id, Sms::TYPE_REGISTRATION, $code->code);

        return $this->response(['status' => self::STATUS_SUCCESS]);
    }
}
