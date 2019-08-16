<?php

return [

    /**
     * Активный сервис.
     */
    'service' => 'ip-api',

    /**
     * конфигурация сервиса ip-api.
     */
    'ip-api' => [
        /**
         * Точка подключения к сервису.
         */
        'end_point' => 'http://ip-api.com/',

        /**
         * Отправляемые заголовки.
         */
        'headers' => [
            'headers' => [
                'User-Agent' => 'Laravel-GeoIP',
            ],
        ],

        /**
         * Формат ответа сервиса.
         */
        'response_format' => 'json',

        'parameters' => [
            /**
             * Идентификатор, обозначающий список используемых полей в сервисе ip-api.com.
             * Настройка инентификатора производиться на http://ip-api.com/docs/api:json .
             */
            'fields' => 49663,

            /**
             * Язык ответа сервиса.
             */
            'lang' => 'ru',
        ],
    ],

];
