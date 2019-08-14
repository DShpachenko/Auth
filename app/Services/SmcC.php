<?php

namespace App\Services;

class SmsC
{
    private const GATWAY_URL = 'http://smsc.ru/sys/send.php';
    private const LOGIN = 'prometheusiam';
    private const PASSWORD = 'ecmrbvehvecmrb';

    public static function send($phone, $message)
    {
        $params = array(
            'login'  => self::LOGIN,
            'psw'    => self::PASSWORD,
            'phones' => $phone,
            'mes'    => $message
        );

        return true;

        try {
            $result = file_get_contents(self::GATWAY_URL, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($params)
                ]
            ]));
        } catch (\Exception $e) {
            \Log::error($e);
        } catch (\Exception $t) {
            \Log::error($t);
        }

        if (substr_count($result, 'OK') == 0) {
            return false;
        }

        return true;
    }
}
