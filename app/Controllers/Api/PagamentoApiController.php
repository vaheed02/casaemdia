<?php

namespace App\Controllers\Api;

use App\Libraries\Payments\MockGateway;
use App\Libraries\Payments\PaymentGatewayFactory;
use App\Models\AgendamentoModel;
use App\Models\PagamentoModel;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class PagamentoApiController extends BaseApiController
{
    public function show(int $agendamentoId): ResponseInterface
    {
        if ($err = $this->autorizarAgendamento($agendamentoId)) {
            return $err;
        }

        $pag = model(PagamentoModel::class)->doAgendamento($agendamentoId);

        return $this->jsonOk($pag);
    }

    public function checkout(int $agendamentoId): ResponseInterface
    {
        $user = $this->apiUser();
        $ag   = model(AgendamentoModel::class)->comRelacoes($agendamentoId);
        if ($ag === null) {
            return $this->jsonError('Agendamento não encontrado.', 404);
        }
        if ($user['role'] !== 'admin' && (int) $ag['cliente_id'] !== (int) $user['id']) {
            return $this->jsonError('Acesso negado.', 403);
        }

        $pag = model(PagamentoModel::class)->doAgendamento($agendamentoId);
        if ($pag === null) {
            return $this->jsonError('Cobrança não encontrada.', 404);
        }

        if (in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            return $this->jsonOk([
                'status'       => $pag['status'],
                'checkout_url' => null,
                'message'      => 'Pagamento já confirmado.',
                'pagamento'    => $pag,
            ]);
        }

        // Mock: confirma sob demanda se pedir
        if (($pag['gateway'] ?? '') === 'mock' && $this->request->getGet('confirm') === '1') {
            $gw = PaymentGatewayFactory::make('mock');
            if ($gw instanceof MockGateway) {
                $pag = $gw->confirmarPagamentoMock($agendamentoId);
            }

            return $this->jsonOk([
                'status'    => $pag['status'] ?? 'autorizado',
                'pagamento' => $pag,
                'message'   => 'Pagamento mock confirmado.',
            ]);
        }

        if (empty($pag['checkout_url']) && ($pag['gateway'] ?? '') === 'mercadopago') {
            try {
                $cliente = model(UsuarioModel::class)->find((int) $ag['cliente_id']);
                $pag = PaymentGatewayFactory::make()->criarCobranca([
                    'agendamento_id'     => $agendamentoId,
                    'valor_bruto'        => (float) $ag['valor_total'],
                    'comissao'           => (float) $ag['comissao_valor'],
                    'liquido'            => (float) $ag['valor_prestador'],
                    'descricao'          => 'Agendamento #' . $agendamentoId,
                    'payer_email'        => $cliente['email'] ?? null,
                    'external_reference' => 'agendamento-' . $agendamentoId,
                ]);
            } catch (RuntimeException $e) {
                return $this->jsonError($e->getMessage(), 400);
            }
        }

        return $this->jsonOk([
            'status'       => $pag['status'],
            'checkout_url' => $pag['checkout_url'] ?? null,
            'pagamento'    => $pag,
            'gateway'      => $pag['gateway'] ?? null,
        ]);
    }

    public function sincronizar(int $agendamentoId): ResponseInterface
    {
        if ($err = $this->autorizarAgendamento($agendamentoId)) {
            return $err;
        }

        try {
            $pag = PaymentGatewayFactory::make()->sincronizarPagamento($agendamentoId);

            return $this->jsonOk($pag);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    private function autorizarAgendamento(int $agendamentoId): ?ResponseInterface
    {
        $user = $this->apiUser();
        $ag   = model(AgendamentoModel::class)->find($agendamentoId);
        if ($ag === null) {
            return $this->jsonError('Agendamento não encontrado.', 404);
        }
        $uid  = (int) $user['id'];
        $role = (string) $user['role'];
        $ok   = $role === 'admin'
            || (int) $ag['cliente_id'] === $uid
            || (int) $ag['prestador_id'] === $uid;

        return $ok ? null : $this->jsonError('Acesso negado.', 403);
    }
}
