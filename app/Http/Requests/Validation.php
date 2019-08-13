<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use App\Models\{User, UserLogin};
use Illuminate\Support\Facades\Validator;

/**
 * Валидация входящего запроса.
 *
 * Class Validation
 * @package App\Http\Requests
 */
class Validation extends Request
{
    /**
     * Ошибки типа "Разное".
     *
     * @var array
     */
    private $anyErrors = [];

    /**
     * Ошибки валидации форм.
     *
     * @var array
     */
    private $formErrors = [];

    /**
     * Правила валидации.
     *
     * @var array
     */
    private $rules = [];

    /**
     * Сообщения для правил валидации.
     *
     * @var array
     */
    private $ruleMessages = [];

    /**
     * Обратабываемый пользователь.
     *
     * @var null
     */
    private $user = null;

    /**
     * Получение списка ошибок.
     *
     * @return array
     */
    public function getErrors()
    {
        return [
            'any' => $this->anyErrors,
            'form' => $this->formErrors,
        ];
    }

    /**
     * Получение массива ошибок с указанным сообщением.
     *
     * @param $message
     * @return array
     */
    public function getErrorsByMessage($message): array
    {
        return [
            'any' => [$message],
            'form' => null,
        ];
    }

    /**
     * Получение найденного пользователя.
     *
     * @return User|null
     */
    public function getUser(): ? User
    {
        return $this->user;
    }

    /**
     * Список правил валидации API ключа.
     *
     * @param $rules
     * @return void
     */
    protected function setRules($rules): void
    {
        $this->rules = $rules;
    }

    /**
     * Возвращает список сообщений валидации.
     *
     * @param $messages
     * @return void
     */
    protected function setMessages($messages): void
    {
        $this->ruleMessages = $messages;
    }

    /**
     * Проверка на превышение количества запросов определенного действия.
     *
     * @param $type
     * @return void
     */
    protected function validateIp($type): void
    {
        if (!UserLogin::checkIpAccess($this->ip(), $type)) {
            $this->anyErrors[] = [__('response.error_number_requests_exceeded')];
        }
    }

    /**
     * Проверка наличия пользователя с указанным номером и статусом.
     *
     * @param $phone
     * @param string $status
     * @return void
     */
    protected function validateUserByPhone($phone, $status = 'empty'): void
    {
        $user = User::findByPhone($phone);

        if (!$user || ($user && $status !== 'empty' && $user->status !== $status)) {
            $this->anyErrors[] = [__('response.user_not_found')];
        } else {
            $this->user = $user;
        }
    }

    /**
     * Валидация массива данных по правилам rules.
     *
     * @param $object
     * @return void
     */
    protected function validateForm($object): void
    {
        $validator = Validator::make($object, $this->rules, $this->ruleMessages);

        if ($validator->fails()) {
            $this->formErrors[] = $validator->errors()->toArray();
        }
    }

    /**
     * Проверка наличия ошибок валидации.
     *
     * @return bool
     */
    protected function fails(): bool
    {
        if (empty($this->anyErrors) && empty($this->formErrors)) {
            return true;
        }

        return false;
    }
}
