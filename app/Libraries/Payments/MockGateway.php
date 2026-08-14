<?php

namespace App\Libraries\Payments;

use App\Models\PagamentoModel;

/**
 * Gateway simulado para desenvolvimento local sem token do Mercado Pago.
 * O cliente PRECISA clicar em "Pagar" (como no fluxo real).
 */
class MockGateway implements PaymentGatewayInterface
{
    public function __construct(private PagamentoModel $pagamentos = new PagamentoModel())
    {
    }

    public function nome(): string
    {
        return 'mock';
    }

    public function criarCobranca(array $dados): array
    {
        $agId = (int) $dados['agendamento_id'];
        $ref  = 'MOCK-' . strtoupper(bin2hex(random_bytes(5)));

        // Status pendente + URL de checkout local (cliente precisa confirmar o pagamento)
        $checkout = rtrim(base_url(), '/') . '/pagamentos/checkout/' . $agId;

        $existente = $this->pagamentos->doAgendamento($agId);
        $row = [
            'agendamento_id'          => $agId,
            'gateway'                 => 'mock',
            'gateway_ref'             => $ref,
            'mp_preference_id'        => null,
            'mp_payment_id'           => null,
            'mp_status'               => 'pending',
            'checkout_url'            => $checkout,
            'status'                  => 'pendente',
            'valor_bruto'             => round((float) $dados['valor_bruto'], 2),
            'valor_comissao'          => round((float) $dados['comissao'], 2),
            'valor_liquido_prestador' => round((float) $dados['liquido'], 2),
            'autorizado_em'           => null,
            'payout_status'           => 'nao_aplicavel',
            'meta_json'               => json_encode([
                'mock'      => true,
                'descricao' => $dados['descricao'] ?? '',
            ], JSON_UNESCAPED_UNICODE),
        ];

        if ($existente) {
            $this->pagamentos->update($existente['id'], $row);

            return $this->pagamentos->find($existente['id']);
        }

        $id = $this->pagamentos->insert($row);

        return $this->pagamentos->find($id);
    }

    /**
     * Confirma pagamento mock (equivale a "approved" no MP).
     */
    public function confirmarPagamentoMock(int $agendamentoId): ?array
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null) {
            return null;
        }

        if (in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            return $pag;
        }

        $ref = $pag['gateway_ref'] ?: ('MOCK-' . strtoupper(bin2hex(random_bytes(4))));
        $this->pagamentos->update($pag['id'], [
            'status'        => 'autorizado',
            'mp_status'     => 'approved',
            'mp_payment_id' => $ref . '-PAY',
            'gateway_ref'   => $ref . '-PAY',
            'autorizado_em' => date('Y-m-d H:i:s'),
            'checkout_url'  => null,
        ]);

        return $this->pagamentos->find($pag['id']);
    }

    public function sincronizarPagamento(int $agendamentoId, ?string $mpPaymentId = null): ?array
    {
        return $this->pagamentos->doAgendamento($agendamentoId);
    }

    public function capturar(int $agendamentoId): ?array
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null || ! in_array($pag['status'], ['autorizado', 'pendente'], true)) {
            return null;
        }

        // Só captura se já estiver autorizado (pago pelo cliente)
        if ($pag['status'] !== 'autorizado') {
            return null;
        }

        $this->pagamentos->update($pag['id'], [
            'status'        => 'capturado',
            'capturado_em'  => date('Y-m-d H:i:s'),
            'payout_status' => 'pendente',
            'gateway_ref'   => ($pag['gateway_ref'] ?? 'MOCK') . '-CAP',
        ]);

        return $this->pagamentos->find($pag['id']);
    }

    public function estornar(int $agendamentoId): ?array
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null) {
            return null;
        }

        if (! in_array($pag['status'], ['autorizado', 'pendente'], true)) {
            return $pag;
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
            'payout_ref'    => $extra['ref'] ?? ('MOCK-PAYOUT-' . strtoupper(bin2hex(random_bytes(4)))),
            'payout_em'     => date('Y-m-d H:i:s'),
            'payout_nota'   => $extra['nota'] ?? 'Repasse mock registrado',
        ]);

        return $this->pagamentos->find($pagamentoId);
    }
}
