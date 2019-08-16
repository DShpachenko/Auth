<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

/**
 * Сервис получения адреса по IP.
 *
 * Class GeoIPApi
 * @package App\Services
 */
class GeoIPApi
{
    /**
     * Метод отправки запроса.
     */
    private const HTTP_METHOD = 'GET';

    /**
     * Ответ сервиса.
     *
     * @var $response;
     */
    private $response;

    /**
     * Точка входа в сервис для обработки обращения.
     *
     * @var string $endPoint
     */
    private $endPoint;

    /**
     * Название сервиса.
     *
     * @var string $service
     */
    private $service;

    /**
     * http client Guzzle.
     *
     * @var $client
     */
    private $client;

    /**
     * Конфигурация подключения к сервису.
     *
     * @var $config.
     */
    private $config;

    /**
     * Инициализация.
     * GeoIPApi constructor.
     */
    public function __construct()
    {
        $this->setConfiguration();
        $this->setClient();
        $this->endPoint = $this->config['end_point'];
    }

    /**
     * Получение конфигурации.
     *
     * @param null $serviceName
     */
    private function setConfiguration($serviceName = null): void
    {
        $this->service = $serviceName ?? config('geoipapi.service');
        $this->config = config('geoipapi.'.$this->service);
    }

    /**
     * Формирование клиента Guzzle.
     *
     * @return void
     */
    private function setClient(): void
    {
        if (isset($this->config['headers'])) {
            $this->client = new Client($this->config['headers']);
        }

        $this->client = new Client();
    }

    /**
     * Отправка запроса к сервису.
     *
     * @return bool | Client
     */
    private function request()
    {

        $this->response = $this->client->request(self::HTTP_METHOD, $this->buildUrl(), [
            'query' => $this->config['parameters']
        ]);

        return $this->response;
    }

    /**
     * Получение строки сервиса.
     *
     * @return string
     */
    private function buildUrl(): string
    {
        return $this->endPoint.$this->config['response_format'].'/'.$this->config['ip'];
    }

    /**
     * Получение названия сервиса.
     *
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Получение точки доступа к сервису.
     *
     * @return string
     */
    public function getEndPoint(): string
    {
        return $this->endPoint;
    }

    /**
     * Получение списка параметров сервиса.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->config;
    }

    /**
     * Установка сервиса.
     *
     * @param $serviceName
     * @return void
     */
    public function setService($serviceName): void
    {
        $this->setConfiguration($serviceName);
        $this->setClient();
    }

    /**
     * Получение информации о месторасположении пользователя через сервис по IP.
     *
     * @param $ip
     * @return mixed|null
     */
    public function getInfo($ip)
    {dd('123');
        dd($this->config);
        $this->config['ip'] = $ip;

        try {
            $response = $this->request();

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            return $response->getBody()->getContents();
        } catch (Exception $e) {
            \Log::error(
                'Ошибка при обращении к сервису ' . $this->service . ' для получения адреса по IP',
                $this->config['parameters']['ip']
            );
        }

        return null;
    }
}
