<?php

namespace App\Libraries\Payments;

use Config\MercadoPago;

class PaymentGatewayFactory
{
    public static function make(?string $driver = null): PaymentGatewayInterface
    {
        $cfg    = config(MercadoPago::class);
        $driver = strtolower(trim((string) ($driver ?? $cfg->driver)));

        if ($driver === 'mercadopago') {
            if (! $cfg->isConfigured()) {
                throw new \RuntimeException(
                    'Mercado Pago selecionado, mas mercadopago.accessToken está vazio no .env'
                );
            }

            return new MercadoPagoGateway();
        }

        // Fallback seguro para desenvolvimento (driver=mock)
        return new MockGateway();
    }

    public static function driverAtivo(): string
    {
        $cfg = config(MercadoPago::class);

        return $cfg->usaMercadoPago() ? 'mercadopago' : 'mock';
    }
}
