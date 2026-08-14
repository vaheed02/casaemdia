<?php

namespace App\Libraries\Payments;

use App\Models\PagamentoModel;
use Config\MercadoPago;
use RuntimeException;

/**
 * Integração Mercado Pago — Checkout Pro.
 *
 * O valor integral do cliente vai para a conta MP da plataforma.
 * A comissão e o líquido do prestador são controlados no ledger interno;
 * o repasse ao prestador é registrado/aprovado pelo admin.
 */
class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function __construct(
        private PagamentoModel $pagamentos = new PagamentoModel(),
        private MercadoPagoClient $client = new MercadoPagoClient(),
        private MercadoPago $config = new MercadoPago(),
    ) {
    }

    public function nome(): string
    {
        return 'mercadopago';
    }

    public function criarCobranca(array $dados): array
    {
        $agId   = (int) $dados['agendamento_id'];
        $bruto  = round((float) $dados['valor_bruto'], 2);
        $base   = rtrim(base_url(), '/');
        $extRef = $dados['external_reference'] ?? ('agendamento-' . $agId);

        if ($bruto < 1) {
            throw new RuntimeException('Valor mínimo para cobrança no Mercado Pago é R$ 1,00.');
        }

        $payload = [
            'items' => [[
                'id'          => 'ag-' . $agId,
                'title'       => mb_substr((string) ($dados['descricao'] ?? ('Serviço #' . $agId)), 0, 120),
                'quantity'    => 1,
                'currency_id' => 'BRL',
                'unit_price'  => $bruto,
            ]],
            'external_reference' => $extRef,
            'notification_url'   => $base . '/webhooks/mercadopago',
            'back_urls'          => [
                'success' => $base . '/pagamentos/retorno?status=success&agendamento=' . $agId,
                'failure' => $base . '/pagamentos/retorno?status=failure&agendamento=' . $agId,
                'pending' => $base . '/pagamentos/retorno?status=pending&agendamento=' . $agId,
            ],
            'statement_descriptor' => mb_substr($this->config->statementDescriptor, 0, 13),
            'metadata'             => [
                'agendamento_id' => $agId,
                'comissao'       => $dados['comissao'],
                'liquido'        => $dados['liquido'],
            ],
        ];

        // auto_return só com URL pública https; em localhost o MP pode rejeitar
        if (str_starts_with($base, 'https://')) {
            $payload['auto_return'] = 'approved';
        }

        if (! empty($dados['payer_email'])) {
            $payload['payer'] = ['email' => $dados['payer_email']];
        }

        $pref = $this->client->createPreference($payload);

        $checkout = $this->config->sandbox
            ? ($pref['sandbox_init_point'] ?? $pref['init_point'] ?? null)
            : ($pref['init_point'] ?? $pref['sandbox_init_point'] ?? null);

        if (! $checkout) {
            throw new RuntimeException('Mercado Pago não retornou URL de checkout (init_point).');
        }

        $row = [
            'agendamento_id'          => $agId,
            'gateway'                 => 'mercadopago',
            'gateway_ref'             => $pref['id'] ?? null,
            'mp_preference_id'        => $pref['id'] ?? null,
            'mp_payment_id'           => null,
            'mp_status'               => 'pending',
            'checkout_url'            => $checkout,
            'status'                  => 'pendente',
            'valor_bruto'             => $bruto,
            'valor_comissao'          => round((float) $dados['comissao'], 2),
            'valor_liquido_prestador' => round((float) $dados['liquido'], 2),
            'payout_status'           => 'nao_aplicavel',
            'meta_json'               => json_encode(['preference' => $pref], JSON_UNESCAPED_UNICODE),
        ];

        $existente = $this->pagamentos->doAgendamento($agId);
        if ($existente && in_array($existente['status'], ['pendente', 'falhou'], true)) {
            $this->pagamentos->update($existente['id'], $row);

            return $this->pagamentos->find($existente['id']);
        }

        if ($existente) {
            // Já pago — não sobrescreve
            return $existente;
        }

        $id = $this->pagamentos->insert($row);

        return $this->pagamentos->find($id);
    }

    public function sincronizarPagamento(int $agendamentoId, ?string $mpPaymentId = null): ?array
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null) {
            return null;
        }

        $paymentId = $mpPaymentId ?: $pag['mp_payment_id'];
        $pay       = null;

        // Sem payment id: busca no MP por external_reference (não depende de webhook)
        if (! $paymentId) {
            $ext = 'agendamento-' . $agendamentoId;
            try {
                $results = $this->client->searchPaymentsByExternalReference($ext);
                // Prefere approved; senão o mais recente
                $approved = null;
                foreach ($results as $r) {
                    if (($r['status'] ?? '') === 'approved') {
                        $approved = $r;
                        break;
                    }
                }
                $pay = $approved ?: ($results[0] ?? null);
                if (is_array($pay) && ! empty($pay['id'])) {
                    $paymentId = (string) $pay['id'];
                }
            } catch (RuntimeException $e) {
                log_message('error', 'MP search external_reference: ' . $e->getMessage());
            }
        }

        if (! $paymentId) {
            return $pag;
        }

        if ($pay === null) {
            $pay = $this->client->getPayment((string) $paymentId);
        }
        $statusMp = (string) ($pay['status'] ?? '');

        $map = [
            'approved'     => 'autorizado',
            'authorized'   => 'autorizado',
            'pending'      => 'pendente',
            'in_process'   => 'pendente',
            'in_mediation' => 'pendente',
            'rejected'     => 'falhou',
            'cancelled'    => 'estornado',
            'refunded'     => 'estornado',
            'charged_back' => 'estornado',
        ];

        $local = $map[$statusMp] ?? $pag['status'];

        // Se já capturado contabilmente, não rebaixa
        if ($pag['status'] === 'capturado' && $local === 'autorizado') {
            $local = 'capturado';
        }

        $update = [
            'mp_payment_id' => (string) ($pay['id'] ?? $paymentId),
            'mp_status'     => $statusMp,
            'gateway_ref'   => (string) ($pay['id'] ?? $pag['gateway_ref']),
            'status'        => $local,
            'meta_json'     => json_encode([
                'preference_id' => $pag['mp_preference_id'],
                'last_payment'  => $pay,
            ], JSON_UNESCAPED_UNICODE),
        ];

        if (in_array($local, ['autorizado', 'capturado'], true) && empty($pag['autorizado_em'])) {
            $update['autorizado_em'] = date('Y-m-d H:i:s');
        }

        $this->pagamentos->update($pag['id'], $update);

        return $this->pagamentos->find($pag['id']);
    }

    public function capturar(int $agendamentoId): ?array
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null) {
            return null;
        }

        if (! in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            if (! empty($pag['mp_payment_id'])) {
                $pag = $this->sincronizarPagamento($agendamentoId) ?? $pag;
            }
        }

        if (! in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            return null;
        }

        if ($pag['status'] === 'capturado' && $pag['payout_status'] === 'pendente') {
            return $pag;
        }

        $this->pagamentos->update($pag['id'], [
            'status'        => 'capturado',
            'capturado_em'  => date('Y-m-d H:i:s'),
            'payout_status' => $pag['payout_status'] === 'pago' ? 'pago' : 'pendente',
        ]);

        return $this->pagamentos->find($pag['id']);
    }

    public function estornar(int $agendamentoId): ?array
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null) {
            return null;
        }

        if (in_array($pag['status'], ['estornado', 'capturado'], true)) {
            if ($pag['status'] === 'capturado') {
                throw new RuntimeException('Pagamento já capturado contabilmente. Estorne pelo painel Mercado Pago e registre manualmente.');
            }

            return $pag;
        }

        if (! empty($pag['mp_payment_id']) && in_array($pag['status'], ['autorizado', 'pendente'], true)) {
            try {
                if ($pag['status'] === 'autorizado' || $pag['mp_status'] === 'approved') {
                    $this->client->refundPayment((string) $pag['mp_payment_id']);
                }
            } catch (RuntimeException $e) {
                if ($pag['status'] === 'autorizado') {
                    throw $e;
                }
            }
        }

        $this->pagamentos->update($pag['id'], [
            'status'        => 'estornado',
            'payout_status' => 'nao_aplicavel',
            'mp_status'     => 'refunded',
        ]);

        return $this->pagamentos->find($pag['id']);
    }

    public function registrarRepasse(int $pagamentoId, array $extra = []): ?array
    {
        $pag = $this->pagamentos->find($pagamentoId);
        if ($pag === null || $pag['status'] !== 'capturado') {
            return null;
        }

        $this->pagamentos->update($pagamentoId, [
            'payout_status' => 'pago',
            'payout_ref'    => $extra['ref'] ?? ('MP-MANUAL-' . date('YmdHis')),
            'payout_em'     => date('Y-m-d H:i:s'),
            'payout_nota'   => $extra['nota'] ?? 'Repasse ao prestador confirmado pelo admin (valor líquido saiu da conta da plataforma).',
        ]);

        return $this->pagamentos->find($pagamentoId);
    }
}
