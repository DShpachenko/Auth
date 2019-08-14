<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{User, SmsCode};
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

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            if (!$user = User::registerUser($request->all())) {
                return $this->response(null, [__('response.error_critical')]);
            }

            $code = SmsCode::addCode($user->id);

            /** @todo Добавить отправку SMS сообщения при успешной регистрации пользователя через RebbitMq вместо этого говна*/

            return $this->response(['status' => self::REGISTRATION_SUCCESS, 'code' => $code]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return $this->response(null, [__('response.error_critical')]);
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

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();

            if (!SmsCode::checkCode($user->id, $request->get('code'))) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.error_failed_sms_code')));
            }

            $user->confirmRegistration();
            /** @todo Добавить обработку с rabbitMq на создание новой информации о пользователе */
            //UserInfo::findByUser($user->id);

            return $this->response(['status' => self::CONFIRMATION_SUCCESS]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return $this->response(null, [__('response.error_critical')]);
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
            $validator = new Forgot();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();
            /** @var SmsCode $lastCode */
            $lastCode = SmsCode::getLastByUser($user->id);

            /** @todo убрать проверку на окружение после интеграции API */
            if (env('APP_ENV') !== 'local' && ((time() - $lastCode->created_at) < SmsCode::SECONDS_BEFORE_NEXT)) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.wait_1_minute')));
            }

            $code = SmsCode::addCode($user->id);

            /** @todo Добавить отправку SMS сообщения при успешной регистрации пользователя через RebbitMq */

            return $this->response(['status' => self::RESEND_SMS_SUCCESS, 'code' => $code]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::error($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }
}
