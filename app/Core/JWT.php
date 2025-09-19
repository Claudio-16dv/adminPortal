<?php

namespace App\Core;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use DateTimeImmutable;

class JWT
{
    public static function generate(array $payload): string
    {
        $secret = (string)($_ENV['JWT_SECRET'] ?? '');
        if (!$secret) {
            throw new \Exception('JWT_SECRET não está definido');
        }

        $issuedAt = new DateTimeImmutable();
        $expiresIn = isset($_ENV['JWT_EXPIRES_IN']) ? (int)$_ENV['JWT_EXPIRES_IN'] : 3600;
        $expire = $issuedAt->modify("+" . $expiresIn . " seconds")->getTimestamp();

        $data = array_merge($payload, [
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expire
        ]);

        return FirebaseJWT::encode($data, $secret, 'HS256');
    }

    public static function verify(string $token): object
    {
        $secret = (string)($_ENV['JWT_SECRET'] ?? '');
        if (!$secret) {
            throw new \Exception('JWT_SECRET não está definido');
        }

        try {
            return FirebaseJWT::decode($token, new Key($secret, 'HS256'));
        } catch (\Exception $e) {
            error_log('Erro na verificação do JWT: ' . $e->getMessage());
            throw new \Exception("Token JWT inválido ou expirado: " . $e->getMessage());
        }
    }
}
