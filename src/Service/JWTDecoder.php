<?php

namespace App\Service;

class JWTDecoder
{
    public static function decode($apiToken)
    {
        $parts = explode('.', $apiToken);
        $payload = json_decode(base64_decode($parts[1]), true, 512, JSON_THROW_ON_ERROR);
        return [
            'exp' => $payload['exp'],
            'username' => $payload['email'],
            'roles' => $payload['roles']
        ];
    }
}