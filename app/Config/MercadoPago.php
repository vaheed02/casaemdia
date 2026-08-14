<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Credenciais e comportamento do Mercado Pago.
 * Nunca versionar o access_token — use o arquivo .env.
 */
class MercadoPago extends BaseConfig
{
    /**
     * Driver: mock | mercadopago
     * mock = desenvolvimento sem token
     */
    public string $driver = 'mock';

    /** Access Token (TEST ou Produção) — de .env mercadopago.accessToken */
    public string $accessToken = '';

    /** Public Key (Checkout) — de .env mercadopago.publicKey */
    public string $publicKey = '';

    /** Webhook secret opcional (x-signature) — de .env mercadopago.webhookSecret */
    public string $webhookSecret = '';

    /** true = sandbox / teste */
    public bool $sandbox = true;

    /**
     * Se true, ao capturar (cliente confirma serviço) tenta marcar repasse
     * automático quando houver e-mail MP do prestador (ainda depende de ação admin se falhar).
     */
    public bool $autoPayout = false;

    /** Statement descriptor / descrição na fatura (máx. ~13 chars no cartão) */
    public string $statementDescriptor = 'CASAEMDIA';

    public function isConfigured(): bool
    {
        // Token basta — o driver escolhido em PaymentGatewayFactory decide o uso
        return trim($this->accessToken) !== '';
    }

    public function usaMercadoPago(): bool
    {
        return $this->driver === 'mercadopago' && $this->isConfigured();
    }

    public function apiBase(): string
    {
        return 'https://api.mercadopago.com';
    }
}
