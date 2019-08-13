<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{User, Sms, SmsCode};
use App\Http\Requests\Registration\{RegistrationRequest, ConfirmationRequest, ResendSmsRequest};

/**
 * Регистрация, подтверждение регистрации, повторная отправка смс для верификации
 *
 * Class RegisterController
 * @package App\Http\Controllers\Api\Auth
 */
class RegisterController extends Controller
{
    /**
     * Регистрация пользователя.
     *
     * @param Request $request
     * @return string
     */
    public function registration(Request $request): string
    {
        try {
            $validator = new RegistrationRequest();

            if ($validator->make($request->all())) {
                return $this->response(null, $validator->getErrors());
            }

            $user = User::registerUser($request);
            $code = SmsCode::addCode($user->id);

            Sms::addSms($user->id, Sms::TYPE_REGISTRATION, $code->code);
            /** @todo Добавить отправку SMS сообщения при успешной регистрации пользователя через RebbitMq */

            return $this->response(['status' => self::REGISTRATION_SUCCESS]);
        } catch (\Exception $e) {
            /** @todo Добавить логирование при не обработанном исключении */
        } catch (\Throwable $t) {
            /** @todo Добавить логирование при не критической ошибке */
        }

        return $this->criticalResponse();
    }

    /**
     * Подтверждение регистрации.
     *
     * @param Request $request
     * @return string
     */
    public function confirmation(Request $request): string
    {
        try {
            $validator = new ConfirmationRequest();

            if ($validator->make($request->all())) {
                return $this->response(null, $validator->getErrors());
            }

            $user = $validator->getUser();

            if (!Sms::checkRepairSms($user->id, Sms::TYPE_REGISTRATION)) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.error_failed_sms_code')));
            }

            if (!SmsCode::checkCode($user->id, $request->get('code'))) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.error_failed_sms_code')));
            }

            $user->confirmRegistration();
            /** @todo Добавить обработку с rabbitMq на создание новой информации о пользователе */
            //UserInfo::findByUser($user->id);

            return $this->response(['status' => self::CONFIRMATION_SUCCESS]);
        } catch (\Exception $e) {
            /** @todo Добавить логирование при не обработанном исключении */
        } catch (\Throwable $t) {
            /** @todo Добавить логирование при не критической ошибке */
        }

        return $this->criticalResponse();
    }

    /**
     * Повторная отправка смс для подтверждения регистрации.
     *
     * @param Request $request
     * @return string
     */
    public function resendingSms(Request $request): string
    {
        try {
            $validator = new ResendSmsRequest();

            if ($validator->make($request->all())) {
                return $this->response(null, $validator->getErrors());
            }

            $user = $validator->getUser();
            $lastCode = SmsCode::getLastByUser($user->id);

            if ((time() - $lastCode->created_at) < SmsCode::SECONDS_BEFORE_NEXT) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.wait_1_minute')));
            }

            $code = SmsCode::addCode($user->id);

            /** @todo Добавить отправку SMS сообщения при успешной регистрации пользователя через RebbitMq */
            Sms::addSms($user->id, Sms::TYPE_REGISTRATION, $code->code);

            return $this->response(['status' => self::RESEND_SMS_SUCCESS]);
        } catch (\Exception $e) {
            /** @todo Добавить логирование при не обработанном исключении */
        } catch (\Throwable $t) {
            /** @todo Добавить логирование при не критической ошибке */
        }

        return $this->criticalResponse();
    }
}
