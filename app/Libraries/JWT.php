<?php

namespace App\Libraries;

if (! function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }
        $res = $known_string ^ $user_string;
        $ret = 0;
        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }

        return $ret === 0;
    }
}

class JWT
{
    public static function palavraChave(): string
    {
        return 'theBookIsOnTheTableIMBEL';
    }

    public static function encode($payload, $key, $alg = 'HS256'): string
    {
        $header    = ['typ' => 'JWT', 'alg' => $alg];
        $segments  = [];
        $segments[] = self::urlsafeB64Encode(json_encode($header));
        $segments[] = self::urlsafeB64Encode(json_encode($payload));
        $signing_input = implode('.', $segments);
        $signature = self::sign($signing_input, $key, $alg);
        $segments[] = self::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    public static function decode($jwt, $key, array $allowed_algs = []): array
    {
        $tks = explode('.', $jwt);

        if (count($tks) !== 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }

        [$headb64, $bodyb64, $cryptob64] = $tks;
        $header  = json_decode(self::urlsafeB64Decode($headb64), true);
        $payload = json_decode(self::urlsafeB64Decode($bodyb64), true);
        $sig     = self::urlsafeB64Decode($cryptob64);

        if (empty($header['alg'])) {
            throw new \DomainException('Empty algorithm');
        }

        if (! in_array($header['alg'], $allowed_algs, true)) {
            throw new \DomainException('Algorithm not allowed');
        }

        if (! self::verify("{$headb64}.{$bodyb64}", $sig, $key, $header['alg'])) {
            throw new \UnexpectedValueException('Signature verification failed');
        }

        return $payload;
    }

    private static function sign($msg, $key, $alg = 'HS256')
    {
        return match ($alg) {
            'HS256'   => hash_hmac('sha256', $msg, $key, true),
            default   => throw new \DomainException('Algorithm not supported'),
        };
    }

    private static function verify($msg, $signature, $key, $alg): bool
    {
        return match ($alg) {
            'HS256'   => hash_equals(hash_hmac('sha256', $msg, $key, true), $signature),
            default   => throw new \DomainException('Algorithm not supported'),
        };
    }

    private static function urlsafeB64Encode($input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function urlsafeB64Decode($input): string
    {
        $remainder = strlen($input) % 4;

        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }
}