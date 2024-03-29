<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{User, SmsCode};
use App\Jobs\{Sms, UserInfo};
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

            /** @var SmsCode $code */
            $code = SmsCode::addCode($user->id, SmsCode::TYPE_REGISTRATION);

            (new Sms())->make(['phone' => $user->phone, 'text' => $code->code]);

            return $this->response(['status' => self::REGISTRATION_SUCCESS, 'code' => $code->code]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
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

            if (!SmsCode::checkCode($user->id, $request->get('code'), [SmsCode::TYPE_REGISTRATION, SmsCode::TYPE_REGISTRATION_RESEND])) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.error_failed_sms_code')));
            }

            if (!$user->confirmRegistration()) {
                return $this->response(null, [__('response.error_critical')]);
            }

            (new UserInfo())->make($user->toArray());

            return $this->response(['status' => self::REGISTRATION_CONFIRMATION_SUCCESS]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
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
            $validator = new ResendSmsRequest();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();
            /** @var SmsCode $lastCode */
            $lastCode = SmsCode::getLastByUser($user->id, [SmsCode::TYPE_REGISTRATION, SmsCode::TYPE_REGISTRATION_RESEND]);

            /** @todo убрать проверку на окружение после интеграции API */
            if (env('APP_ENV') !== 'local' && ((time() - $lastCode->created_at) < SmsCode::SECONDS_BEFORE_NEXT)) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.wait_1_minute')));
            }

            /** @var SmsCode $code */
            $code = SmsCode::addCode($user->id, SmsCode::TYPE_REGISTRATION_RESEND);

            (new Sms())->make(['phone' => $user->phone, 'text' => $code->code]);

            return $this->response(['status' => self::REGISTRATION_RESEND_SMS_SUCCESS, 'code' => $code->code]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }
}
