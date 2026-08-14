<?php

namespace App\Controllers;

use App\Libraries\Payments\MockGateway;
use App\Libraries\Payments\PaymentGatewayFactory;
use App\Models\AgendamentoModel;
use App\Models\PagamentoModel;
use App\Models\UsuarioModel;
use RuntimeException;

class PaymentController extends BaseController
{
    /**
     * Inicia / reabre o checkout (Mercado Pago ou mock).
     */
    public function checkout(int $agendamentoId): mixed
    {
        $ag = model(AgendamentoModel::class)->comRelacoes($agendamentoId);
        if ($ag === null) {
            return redirect()->to(base_url('agendamentos'))->with('error', 'Agendamento não encontrado.');
        }

        $uid  = $this->usuarioId();
        $role = $this->usuarioRole();
        if ($role !== 'admin' && (int) $ag['cliente_id'] !== $uid) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acesso negado.');
        }

        $pagModel = model(PagamentoModel::class);
        $pag      = $pagModel->doAgendamento($agendamentoId);

        if ($pag === null) {
            // Cria cobrança se o agendamento ficou sem pagamento
            try {
                $pag = $this->recriarCobranca($ag);
            } catch (RuntimeException $e) {
                return redirect()->to(base_url('agendamentos/' . $agendamentoId))
                    ->with('error', $e->getMessage());
            }
        }

        if (in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            return redirect()->to(base_url('agendamentos/' . $agendamentoId))
                ->with('success', 'Pagamento já confirmado. O prestador já pode aceitar o serviço.');
        }

        // Mock: tela local de confirmação (simula o checkout)
        if (($pag['gateway'] ?? '') === 'mock') {
            return $this->renderPage('pages/pagamentos/mock_checkout', [
                'title'      => 'Pagar agendamento #' . $agendamentoId,
                'activePage' => 'meus-agendamentos',
                'ag'         => $ag,
                'pagamento'  => $pag,
            ]);
        }

        // Mercado Pago: se não tem URL, regenera preference
        if (empty($pag['checkout_url']) || in_array($pag['status'], ['pendente', 'falhou'], true)) {
            try {
                if (empty($pag['checkout_url']) || $pag['status'] === 'falhou') {
                    $pag = $this->recriarCobranca($ag);
                }
            } catch (RuntimeException $e) {
                return redirect()->to(base_url('agendamentos/' . $agendamentoId))
                    ->with('error', 'Não foi possível gerar o checkout: ' . $e->getMessage());
            }
        }

        if (! empty($pag['checkout_url'])) {
            return redirect()->to($pag['checkout_url']);
        }

        return redirect()->to(base_url('agendamentos/' . $agendamentoId))
            ->with('error', 'Link de pagamento indisponível. Verifique mercadopago.driver e o accessToken no .env.');
    }

    /**
     * Confirma pagamento no gateway mock (cliente clica "Já paguei / simular").
     */
    public function confirmarMock(int $agendamentoId): mixed
    {
        $ag = model(AgendamentoModel::class)->find($agendamentoId);
        if ($ag === null) {
            return redirect()->to(base_url('agendamentos'))->with('error', 'Agendamento não encontrado.');
        }

        $uid  = $this->usuarioId();
        $role = $this->usuarioRole();
        if ($role !== 'admin' && (int) $ag['cliente_id'] !== $uid) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acesso negado.');
        }

        $pag = model(PagamentoModel::class)->doAgendamento($agendamentoId);
        if ($pag === null || ($pag['gateway'] ?? '') !== 'mock') {
            return redirect()->to(base_url('agendamentos/' . $agendamentoId))
                ->with('error', 'Este pagamento não é mock. Use o checkout do Mercado Pago.');
        }

        $gw = PaymentGatewayFactory::make('mock');
        if ($gw instanceof MockGateway) {
            $gw->confirmarPagamentoMock($agendamentoId);
        }

        return redirect()->to(base_url('agendamentos/' . $agendamentoId))
            ->with('success', 'Pagamento confirmado! O valor está retido na plataforma. O prestador já pode aceitar.');
    }

    /**
     * Retorno do Checkout Pro (success/failure/pending).
     */
    public function retorno(): mixed
    {
        $status    = (string) $this->request->getGet('status');
        $agId      = (int) $this->request->getGet('agendamento');
        $paymentId = $this->request->getGet('payment_id')
            ?? $this->request->getGet('collection_id');

        if ($agId > 0) {
            try {
                // Com ou sem payment_id: busca no MP por external_reference se precisar
                PaymentGatewayFactory::make()->sincronizarPagamento(
                    $agId,
                    ($paymentId && $paymentId !== 'null') ? (string) $paymentId : null
                );
            } catch (RuntimeException $e) {
                log_message('error', 'MP retorno sync: ' . $e->getMessage());
            }
        }

        $msg = match ($status) {
            'success' => 'Pagamento recebido! A plataforma reteve o valor; o prestador poderá aceitar o serviço.',
            'pending' => 'Pagamento em análise no Mercado Pago. Atualize esta página em instantes.',
            'failure' => 'Pagamento não concluído. Você pode tentar novamente pelo botão Pagar.',
            default   => 'Retorno do pagamento processado.',
        };

        $type = $status === 'failure' ? 'error' : 'success';

        if ($agId > 0) {
            return redirect()->to(base_url('agendamentos/' . $agId))->with($type, $msg);
        }

        return redirect()->to(base_url('dashboard'))->with($type, $msg);
    }

    /**
     * Cliente pede re-sincronização do status (útil se webhook não chegou).
     */
    public function sincronizar(int $agendamentoId): mixed
    {
        $ag = model(AgendamentoModel::class)->find($agendamentoId);
        if ($ag === null) {
            return redirect()->to(base_url('agendamentos'))->with('error', 'Agendamento não encontrado.');
        }

        $uid  = $this->usuarioId();
        $role = $this->usuarioRole();
        if ($role !== 'admin' && (int) $ag['cliente_id'] !== $uid && (int) $ag['prestador_id'] !== $uid) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acesso negado.');
        }

        try {
            $pag = PaymentGatewayFactory::make()->sincronizarPagamento($agendamentoId);
            $st  = $pag['status'] ?? 'desconhecido';

            return redirect()->to(base_url('agendamentos/' . $agendamentoId))
                ->with('success', 'Status sincronizado: ' . $st);
        } catch (RuntimeException $e) {
            return redirect()->to(base_url('agendamentos/' . $agendamentoId))
                ->with('error', $e->getMessage());
        }
    }

    private function recriarCobranca(array $ag): array
    {
        $cliente = model(UsuarioModel::class)->find((int) $ag['cliente_id']);
        $tipoLbl = config('Servicos')->label((string) $ag['tipo_servico']);
        $gw      = PaymentGatewayFactory::make();

        return $gw->criarCobranca([
            'agendamento_id'     => (int) $ag['id'],
            'valor_bruto'        => (float) $ag['valor_total'],
            'comissao'           => (float) $ag['comissao_valor'],
            'liquido'            => (float) $ag['valor_prestador'],
            'descricao'          => $tipoLbl . ' — Agendamento #' . $ag['id'],
            'payer_email'        => $cliente['email'] ?? null,
            'external_reference' => 'agendamento-' . $ag['id'],
        ]);
    }
}
