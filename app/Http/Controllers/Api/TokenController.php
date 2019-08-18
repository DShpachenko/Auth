<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ {UserTokens, LoginWhiteList};
use App\Http\Requests\Token\TokenRequest;

/**
 * Обновление токена.
 *
 * Class TokenController
 * @package App\Http\Controllers\Api
 */
class TokenController extends Controller
{
    /**
     * Смена токена.
     *
     * @param Request $request
     * @return string
     */
    public function update(Request $request):string
    {
        try {
            $validator = new TokenRequest();

            if (!$validator->make($request)) {
                return $this->response(null, $validator->getErrors());
            }

            $tokenId = UserTokens::tokenDecomposition($request->get('token'));
            $comparativeToken = UserTokens::generateJwtToken($tokenId);

            if ($request->get('token') !== $comparativeToken) {
                return $this->response(null, [__('response.failed_token_update')]);
            }

            $oldToken = UserTokens::find($tokenId);

            if ($oldToken->status === UserTokens::STATUS_END) {
                return $this->response(null, [__('response.token_failed')]);
            }

            if ((time() - $oldToken->create_time) > UserTokens::REFRESH_TIME) {
                return $this->response(null, [__('response.token_update_lost_time')]);
            }

            if (!LoginWhiteList::check($tokenId, $oldToken->user_id, $request->ip())) {
                return $this->response(null, [__('response.token_update_unknown_ip')]);
            }

            $oldToken->disableToken();
            $token = UserTokens::add($oldToken->user_id);

            if (!$token) {
                LoginWhiteList::add($oldToken->user_id, $token->_id, $request->ip(), LoginWhiteList::STATUS_FAILED);

                return $this->response(null, $validator->getErrorsByMessage(__('response.failed_login_pass')));
            }

            LoginWhiteList::add($oldToken->user_id, $token->_id, $request->ip(), LoginWhiteList::STATUS_SUCCESS);

            return $this->response([
                'status' => self::REFRESH_TOKEN_SUCCESS,
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
