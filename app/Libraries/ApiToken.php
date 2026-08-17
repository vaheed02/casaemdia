<?php

namespace App\Libraries;

/**
 * JWT simples para a API mobile (HS256).
 * Segredo: env api.jwtSecret ou fallback (troque em produção no .env).
 */
class ApiToken
{
    public static function secret(): string
    {
        $env = env('api.jwtSecret', '');
        if (is_string($env) && trim($env) !== '') {
            return trim($env);
        }

        return JWT::palavraChave() . '.casaemdia.api';
    }

    /**
     * @param array{id:int|string,nome?:string,email?:string,role?:string} $usuario
     */
    public static function issue(array $usuario, int $ttlSeconds = 60 * 60 * 24 * 30): string
    {
        $now = time();
        $payload = [
            'sub'   => (int) $usuario['id'],
            'id'    => (int) $usuario['id'],
            'nome'  => $usuario['nome'] ?? '',
            'email' => $usuario['email'] ?? '',
            'role'  => $usuario['role'] ?? 'cliente',
            'iat'   => $now,
            'exp'   => $now + $ttlSeconds,
        ];

        return JWT::encode($payload, self::secret(), 'HS256');
    }

    public static function decode(string $token): array
    {
        $payload = JWT::decode($token, self::secret(), ['HS256']);

        if (! empty($payload['exp']) && time() > (int) $payload['exp']) {
            throw new \UnexpectedValueException('Token expirado');
        }

        return $payload;
    }
}
