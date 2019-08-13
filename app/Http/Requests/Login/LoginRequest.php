<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация входящего запроса для авторизации.
 *
 * Class LoginRequest
 * @package App\Http\Requests
 */
class LoginRequest extends FormRequest
{
    /**
     * Проверка авторизации пользователя.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Список правил валидации API ключа.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = User::RULES;
        $rules['name'] .= Rule::unique('users')->ignore($this->user);
        $rules['phone'] .= Rule::unique('users')->ignore($this->user);
        $rules['email'] .= Rule::unique('users')->ignore($this->user);
        $rules['password'] .= $this->route()->user ? 'nullable' : 'required';

        return $rules;
    }

    /**
     * Возвращает список сообщений валидации.
     *
     * @return array
     */
    public function messages(): array
    {
        return User::RULES_MESSAGES;
    }
}
