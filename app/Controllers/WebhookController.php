<?php

namespace App\Controllers;

use App\Libraries\Payments\PaymentGatewayFactory;
use App\Models\PagamentoModel;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

/**
 * Webhooks públicos (sem auth de sessão).
 */
class WebhookController extends BaseController
{
    /**
     * Notificações do Mercado Pago.
     * Configurar no painel MP: https://seudominio/webhooks/mercadopago
     */
    public function mercadopago(): ResponseInterface
    {
        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }
        if (! is_array($payload)) {
            $payload = [];
        }

        // Query string style: ?topic=payment&id=123
        $topic = $this->request->getGet('topic')
            ?? $this->request->getGet('type')
            ?? ($payload['type'] ?? ($payload['topic'] ?? null));
        $id = $this->request->getGet('id')
            ?? $this->request->getGet('data.id')
            ?? ($payload['data']['id'] ?? ($payload['id'] ?? null));

        log_message('info', 'MP Webhook: topic={topic} id={id} body={body}', [
            'topic' => (string) $topic,
            'id'    => (string) $id,
            'body'  => json_encode($payload),
        ]);

        if ($topic && in_array((string) $topic, ['payment', 'merchant_order'], true) === false
            && ! str_contains((string) $topic, 'payment')) {
            return $this->response->setStatusCode(200)->setJSON(['ok' => true, 'ignored' => true]);
        }

        if (! $id) {
            return $this->response->setStatusCode(200)->setJSON(['ok' => true, 'empty' => true]);
        }

        try {
            $gw  = PaymentGatewayFactory::make('mercadopago');
            // localiza agendamento pelo payment id ou consulta payment
            $pag = model(PagamentoModel::class)->where('mp_payment_id', (string) $id)->first();

            if ($pag === null) {
                // tenta pelo external_reference via API
                $client = new \App\Libraries\Payments\MercadoPagoClient();
                $pay    = $client->getPayment((string) $id);
                $ext    = (string) ($pay['external_reference'] ?? '');
                $agId   = 0;
                if (preg_match('/agendamento-(\d+)/', $ext, $m)) {
                    $agId = (int) $m[1];
                } elseif (! empty($pay['metadata']['agendamento_id'])) {
                    $agId = (int) $pay['metadata']['agendamento_id'];
                }

                if ($agId > 0) {
                    $gw->sincronizarPagamento($agId, (string) $id);
                }
            } else {
                $gw->sincronizarPagamento((int) $pag['agendamento_id'], (string) $id);
            }
        } catch (RuntimeException $e) {
            log_message('error', 'MP Webhook error: ' . $e->getMessage());
            // 200 evita reenvios infinitos em erro de config; loga para diagnóstico
        }

        return $this->response->setStatusCode(200)->setJSON(['ok' => true]);
    }
}
