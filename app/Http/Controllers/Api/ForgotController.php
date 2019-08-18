<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {User, UserTokens};
use App\Models\ {SmsCode};
use App\Http\Requests\Forgot\{ForgotRequest, ConfirmationForgotRequest, ResendSmsForgotRequest};

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
        try {
            $validator = new ForgotRequest();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();
            $code = SmsCode::addCode($user->id, SmsCode::TYPE_PASSWORD_RECOVERY);

            /** @todo Добавить отправку SMS сообщения при успешной регистрации пользователя через RabbitMq */

            return $this->response(['status' => self::FORGOT_SEND_SMS_SUCCESS, 'code' => $code->code]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }

    /**
     * Подтверждение сброса пароля (установка нового пароля).
     *
     * @param Request $request
     * @return string
     */
    public function confirmation(Request $request):string
    {
        try {
            $validator = new ConfirmationForgotRequest();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();

            if (!SmsCode::checkCode($user->id, $request->get('code'), [SmsCode::TYPE_PASSWORD_RECOVERY, SmsCode::TYPE_PASSWORD_RECOVERY_RESEND])) {
                return $this->response(null, $validator->getErrorsByMessage(__('response.error_failed_sms_code')));
            }

            if (!$user->updatePassword($request->get('password'))) {
                return $this->response(null, [__('response.error_critical')]);
            }

            UserTokens::disableUserTokens($user->id);

            return $this->response(['status' => self::FORGOT_CONFIRMATION_SUCCESS]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }

    /**
     * Повторная отправка смс с кодом для подтверждения сброса пароля.
     *
     * @param Request $request
     * @return string
     */
    public function resendingSms(Request $request):string
    {
        try {
            $validator = new ResendSmsForgotRequest();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            /** @var User $user */
            $user = $validator->getUser();
            $code = SmsCode::addCode($user->id);

            /** @todo Добавить отправку SMS сообщения при успешной регистрации пользователя через RabbitMq */

            return $this->response(['status' => self::FORGOT_RESEND_SMS_SUCCESS, 'code' => $code->code]);
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Throwable $t) {
            \Log::critical($t);
        }

        return $this->response(null, [__('response.error_critical')]);
    }
}
