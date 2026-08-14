<?php

namespace App\Libraries;

use App\Libraries\Payments\PaymentGatewayFactory;
use App\Libraries\Payments\PaymentGatewayInterface;
use App\Models\AgendamentoModel;
use App\Models\AvaliacaoModel;
use App\Models\ConfiguracaoModel;
use App\Models\PagamentoModel;
use App\Models\PerfilPrestadorModel;
use App\Models\UsuarioModel;
use RuntimeException;

/**
 * Ciclo: agenda → pagamento (MP retém na conta da plataforma) → prestador executa
 * → cliente confirma → comissão retida / líquido a repassar (admin).
 */
class AgendamentoService
{
    private PaymentGatewayInterface $gateway;

    public function __construct(
        private AgendamentoModel $agendamentos = new AgendamentoModel(),
        private PerfilPrestadorModel $perfis = new PerfilPrestadorModel(),
        private AvaliacaoModel $avaliacoes = new AvaliacaoModel(),
        private PagamentoModel $pagamentos = new PagamentoModel(),
        ?PaymentGatewayInterface $gateway = null,
    ) {
        $this->gateway = $gateway ?? PaymentGatewayFactory::make();
    }

    public function criar(array $dados): array
    {
        $pct  = model(ConfiguracaoModel::class)->comissaoPercentual();
        $vals = $this->agendamentos->calcularValores((float) $dados['valor_total'], $pct);
        $db   = db_connect();

        $db->transStart();

        $id = $this->agendamentos->insert([
            'cliente_id'          => $dados['cliente_id'],
            'prestador_id'        => $dados['prestador_id'],
            'tipo_servico'        => $dados['tipo_servico'],
            'endereco_id'         => $dados['endereco_id'] ?? null,
            'data_servico'        => $dados['data_servico'],
            'hora_inicio'         => $dados['hora_inicio'],
            'duracao_horas'       => $dados['duracao_horas'] ?? 4,
            'valor_total'         => $dados['valor_total'],
            'comissao_percentual' => $pct,
            'comissao_valor'      => $vals['comissao_valor'],
            'valor_prestador'     => $vals['valor_prestador'],
            'status'              => 'pendente',
            'observacoes_cliente' => $dados['observacoes_cliente'] ?? null,
        ]);

        $cliente = model(UsuarioModel::class)->find((int) $dados['cliente_id']);
        $tipoLbl = config('Servicos')->label((string) $dados['tipo_servico']);

        try {
            $this->gateway->criarCobranca([
                'agendamento_id'     => (int) $id,
                'valor_bruto'        => (float) $dados['valor_total'],
                'comissao'           => (float) $vals['comissao_valor'],
                'liquido'            => (float) $vals['valor_prestador'],
                'descricao'          => $tipoLbl . ' — Agendamento #' . $id,
                'payer_email'        => $cliente['email'] ?? null,
                'external_reference' => 'agendamento-' . $id,
            ]);
        } catch (RuntimeException $e) {
            $db->transRollback();
            throw new RuntimeException('Falha ao criar cobrança: ' . $e->getMessage(), 0, $e);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Não foi possível gravar o agendamento.');
        }

        return $this->agendamentos->comRelacoes((int) $id);
    }

    public function exigirPagamentoRecebido(int $agendamentoId): void
    {
        $pag = $this->pagamentos->doAgendamento($agendamentoId);
        if ($pag === null) {
            throw new RuntimeException('Pagamento não encontrado para este agendamento.');
        }

        if (! in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            // tenta sincronizar (MP)
            $pag = $this->gateway->sincronizarPagamento($agendamentoId) ?? $pag;
        }

        if (! in_array($pag['status'], ['autorizado', 'capturado'], true)) {
            throw new RuntimeException('Aguarde a confirmação do pagamento do cliente (Mercado Pago) antes de continuar.');
        }
    }

    public function aceitar(int $id, int $prestadorId): array
    {
        $a = $this->obterDoPrestador($id, $prestadorId);
        $this->exigeStatus($a, ['pendente']);
        $this->exigirPagamentoRecebido($id);

        $this->agendamentos->update($id, [
            'status'    => 'aceito',
            'aceito_em' => date('Y-m-d H:i:s'),
        ]);

        return $this->agendamentos->comRelacoes($id);
    }

    public function rejeitar(int $id, int $prestadorId, ?string $motivo = null): array
    {
        $a = $this->obterDoPrestador($id, $prestadorId);
        $this->exigeStatus($a, ['pendente']);

        $this->agendamentos->update($id, [
            'status'          => 'rejeitado',
            'motivo_rejeicao' => $motivo,
        ]);
        $this->gateway->estornar($id);

        return $this->agendamentos->comRelacoes($id);
    }

    public function cancelar(int $id, int $usuarioId, string $role): array
    {
        $a = $this->agendamentos->find($id);
        if ($a === null) {
            throw new RuntimeException('Agendamento não encontrado.');
        }

        $dono = ($role === 'cliente' && (int) $a['cliente_id'] === $usuarioId)
            || ($role === 'prestador' && (int) $a['prestador_id'] === $usuarioId)
            || $role === 'admin';

        if (! $dono) {
            throw new RuntimeException('Sem permissão para cancelar.');
        }

        $this->exigeStatus($a, ['pendente', 'aceito']);

        $this->agendamentos->update($id, ['status' => 'cancelado']);
        $this->gateway->estornar($id);

        return $this->agendamentos->comRelacoes($id);
    }

    public function iniciar(int $id, int $prestadorId): array
    {
        $a = $this->obterDoPrestador($id, $prestadorId);
        $this->exigeStatus($a, ['aceito']);
        $this->exigirPagamentoRecebido($id);

        $this->agendamentos->update($id, [
            'status'      => 'em_andamento',
            'iniciado_em' => date('Y-m-d H:i:s'),
        ]);

        return $this->agendamentos->comRelacoes($id);
    }

    public function concluir(int $id, int $prestadorId): array
    {
        $a = $this->obterDoPrestador($id, $prestadorId);
        $this->exigeStatus($a, ['em_andamento']);

        $this->agendamentos->update($id, [
            'status'       => 'aguardando_confirmacao',
            'concluido_em' => date('Y-m-d H:i:s'),
        ]);

        return $this->agendamentos->comRelacoes($id);
    }

    /**
     * Cliente confirma → comissão retida na plataforma; líquido fica a repassar ao prestador.
     */
    public function confirmar(int $id, int $clienteId, ?int $nota = null, ?string $comentario = null): array
    {
        $a = $this->agendamentos->find($id);
        if ($a === null || (int) $a['cliente_id'] !== $clienteId) {
            throw new RuntimeException('Agendamento não encontrado.');
        }
        $this->exigeStatus($a, ['aguardando_confirmacao']);
        $this->exigirPagamentoRecebido($id);

        $captura = $this->gateway->capturar($id);
        if ($captura === null) {
            throw new RuntimeException('Não foi possível capturar/contabilizar o pagamento. Verifique o status no Mercado Pago.');
        }

        $now = date('Y-m-d H:i:s');
        $this->agendamentos->update($id, [
            'status'        => 'pago',
            'confirmado_em' => $now,
            'pago_em'       => $now,
        ]);

        if ($nota !== null && $nota >= 1 && $nota <= 5) {
            $this->avaliacoes->insert([
                'agendamento_id' => $id,
                'cliente_id'     => $clienteId,
                'prestador_id'   => $a['prestador_id'],
                'nota'           => $nota,
                'comentario'     => $comentario,
            ]);
            $this->avaliacoes->recalcularMediaPrestador((int) $a['prestador_id']);
        }

        return $this->agendamentos->comRelacoes($id);
    }

    public function valorSugerido(int $prestadorUsuarioId, string $tipo): float
    {
        $perfil = $this->perfis->findByUsuario($prestadorUsuarioId);
        if ($perfil === null) {
            return 0;
        }

        return $this->perfis->valorDoTipo($perfil, $tipo);
    }

    public function gateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }

    private function obterDoPrestador(int $id, int $prestadorId): array
    {
        $a = $this->agendamentos->find($id);
        if ($a === null || (int) $a['prestador_id'] !== $prestadorId) {
            throw new RuntimeException('Agendamento não encontrado.');
        }

        return $a;
    }

    private function exigeStatus(array $a, array $permitidos): void
    {
        if (! in_array($a['status'], $permitidos, true)) {
            throw new RuntimeException(
                'Transição inválida. Status atual: ' . $a['status']
            );
        }
    }
}
