<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    
    'crocopay' => [
        'client_id' => env('CROCOPAY_CLIENT_ID', 'Arbb4ln3iamjbNPVOCk88tKbVpDloE'),
        'client_secret' => env('CROCOPAY_CLIENT_SECRET', 'obhVOM7ZHXE0bch7RUhvBr7YbSeqOHfwYRyU4LxIonvhQyZ1S395L0TOzQTc0cbudZBwtL8aP9l6vfofaZAVU9ub5CXSQo9Qqqqb'),
    ],


   'telegram' => [
    'bot' => env('TELEGRAM_BOT_NAME', 'magnife_bot'),
    'client_id' => null,
    'token' => env('TELEGRAM_TOKEN', '8060803842:AAEsyu_czkQd4HgEp4uytUSzEX66ur1VHN4'),
    'redirect' => env('TELEGRAM_REDIRECT_URI', 'https://magnife.ru/tg_auth'),
    ],



    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
