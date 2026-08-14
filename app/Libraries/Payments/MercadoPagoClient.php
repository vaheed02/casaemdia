<?php

namespace App\Libraries\Payments;

use Config\MercadoPago;
use RuntimeException;

/**
 * Cliente HTTP da API REST do Mercado Pago.
 */
class MercadoPagoClient
{
    public function __construct(private MercadoPago $config = new MercadoPago())
    {
    }

    public function request(string $method, string $path, ?array $body = null, array $headers = []): array
    {
        if (trim($this->config->accessToken) === '') {
            throw new RuntimeException('Mercado Pago não configurado: defina mercadopago.accessToken no .env');
        }

        $url = rtrim($this->config->apiBase(), '/') . '/' . ltrim($path, '/');

        $ch = curl_init($url);
        $hdr = array_merge([
            'Authorization: Bearer ' . $this->config->accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Idempotency-Key: ' . bin2hex(random_bytes(16)),
        ], $headers);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $hdr,
            CURLOPT_TIMEOUT        => 45,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Falha de comunicação com Mercado Pago: ' . $err);
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            $data = ['_raw' => $raw];
        }

        $data['_http_status'] = $code;

        if ($code >= 400) {
            $msg = $data['message'] ?? ($data['error'] ?? 'Erro Mercado Pago HTTP ' . $code);
            if (! empty($data['cause'][0]['description'])) {
                $msg .= ' — ' . $data['cause'][0]['description'];
            }
            throw new RuntimeException($msg);
        }

        return $data;
    }

    public function createPreference(array $payload): array
    {
        return $this->request('POST', '/checkout/preferences', $payload);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', '/v1/payments/' . rawurlencode($paymentId));
    }

    /**
     * Busca pagamentos pelo external_reference (ex.: agendamento-12).
     * Útil quando o webhook não chegou e não há mp_payment_id local.
     *
     * @return list<array<string, mixed>>
     */
    public function searchPaymentsByExternalReference(string $externalReference): array
    {
        $qs = http_build_query([
            'external_reference' => $externalReference,
            'sort'               => 'date_created',
            'criteria'           => 'desc',
            'range'              => 'date_created',
            'begin_date'         => 'NOW-6MONTHS',
            'end_date'           => 'NOW',
        ]);

        $data = $this->request('GET', '/v1/payments/search?' . $qs);
        $results = $data['results'] ?? [];

        return is_array($results) ? $results : [];
    }

    public function getMerchantOrder(string $orderId): array
    {
        return $this->request('GET', '/merchant_orders/' . rawurlencode($orderId));
    }

    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        $body = $amount !== null ? ['amount' => round($amount, 2)] : new \stdClass();

        return $this->request('POST', '/v1/payments/' . rawurlencode($paymentId) . '/refunds', (array) $body);
    }
}
