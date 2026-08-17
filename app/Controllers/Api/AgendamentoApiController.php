<?php

namespace App\Controllers\Api;

use App\Libraries\AgendamentoService;
use App\Models\AgendamentoModel;
use App\Models\EnderecoModel;
use App\Models\PagamentoModel;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Servicos;
use RuntimeException;

class AgendamentoApiController extends BaseApiController
{
    public function index(): ResponseInterface
    {
        $user = $this->apiUser();
        $uid  = (int) $user['id'];
        $role = (string) $user['role'];
        $ag   = model(AgendamentoModel::class);

        if ($role === 'prestador') {
            $lista = $ag->doPrestador($uid);
        } elseif ($role === 'admin') {
            $lista = $ag->db->table('agendamentos a')
                ->select('a.*, c.nome AS cliente_nome, p.nome AS prestador_nome')
                ->join('usuarios c', 'c.id = a.cliente_id')
                ->join('usuarios p', 'p.id = a.prestador_id')
                ->orderBy('a.id', 'DESC')
                ->get()
                ->getResultArray();
        } else {
            $lista = $ag->doCliente($uid);
        }

        return $this->jsonOk($lista);
    }

    public function show(int $id): ResponseInterface
    {
        $user = $this->apiUser();
        $ag   = model(AgendamentoModel::class)->comRelacoes($id);
        if ($ag === null) {
            return $this->jsonError('Agendamento não encontrado.', 404);
        }

        $uid  = (int) $user['id'];
        $role = (string) $user['role'];
        $ok   = $role === 'admin'
            || (int) $ag['cliente_id'] === $uid
            || (int) $ag['prestador_id'] === $uid;

        if (! $ok) {
            return $this->jsonError('Acesso negado.', 403);
        }

        $pag = model(PagamentoModel::class)->doAgendamento($id);

        return $this->jsonOk([
            'agendamento' => $ag,
            'pagamento'   => $pag,
            'status_labels' => config(Servicos::class)->statusLabels,
        ]);
    }

    public function create(): ResponseInterface
    {
        $user = $this->apiUser();
        if (! in_array($user['role'], ['cliente', 'admin'], true)) {
            return $this->jsonError('Apenas clientes podem agendar.', 403);
        }

        $json = $this->request->getJSON(true) ?? [];
        $cfg  = config(Servicos::class);

        $prestadorId = (int) ($json['prestador_id'] ?? 0);
        $tipo        = (string) ($json['tipo_servico'] ?? 'diarista');
        $enderecoId  = (int) ($json['endereco_id'] ?? 0);
        $data        = (string) ($json['data_servico'] ?? '');
        $hora        = (string) ($json['hora_inicio'] ?? '');
        $duracao     = (float) ($json['duracao_horas'] ?? 0);
        $obs         = trim((string) ($json['observacoes_cliente'] ?? ''));
        $valorCustom = $json['valor_total'] ?? null;

        if (! $cfg->ehTipoValido($tipo)) {
            return $this->jsonError('Tipo de serviço inválido.', 422);
        }

        $prestador = model(UsuarioModel::class)->find($prestadorId);
        if ($prestador === null || $prestador['role'] !== 'prestador') {
            return $this->jsonError('Prestador inválido.', 422);
        }

        $endereco = model(EnderecoModel::class)->find($enderecoId);
        if ($endereco === null || (int) $endereco['usuario_id'] !== (int) $user['id']) {
            return $this->jsonError('Endereço inválido.', 422);
        }

        if ($data === '' || $hora === '') {
            return $this->jsonError('Informe data e horário.', 422);
        }

        $svc   = new AgendamentoService();
        $valor = $svc->valorSugerido($prestadorId, $tipo);
        if ($valorCustom !== null && $valorCustom !== '') {
            $valor = (float) str_replace(',', '.', (string) $valorCustom);
        }
        if ($valor < 1) {
            return $this->jsonError('Valor inválido (mín. R$ 1,00).', 422);
        }

        try {
            $ag = $svc->criar([
                'cliente_id'          => (int) $user['id'],
                'prestador_id'        => $prestadorId,
                'tipo_servico'        => $tipo,
                'endereco_id'         => $enderecoId,
                'data_servico'        => $data,
                'hora_inicio'         => $hora,
                'duracao_horas'       => $duracao > 0 ? $duracao : $cfg->duracaoPadrao($tipo),
                'valor_total'         => $valor,
                'observacoes_cliente' => $obs,
            ]);

            $pag = model(PagamentoModel::class)->doAgendamento((int) $ag['id']);

            return $this->jsonOk([
                'agendamento'  => $ag,
                'pagamento'    => $pag,
                'checkout_url' => $pag['checkout_url'] ?? null,
            ], 201);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    public function acao(int $id, string $acao): ResponseInterface
    {
        $user = $this->apiUser();
        $uid  = (int) $user['id'];
        $role = (string) $user['role'];
        $json = $this->request->getJSON(true) ?? [];
        $svc  = new AgendamentoService();

        try {
            $result = match ($acao) {
                'aceitar' => $this->exigePrestador($role) ? $svc->aceitar($id, $uid) : throw new RuntimeException('Apenas prestador.'),
                'rejeitar' => $this->exigePrestador($role) ? $svc->rejeitar($id, $uid, $json['motivo'] ?? null) : throw new RuntimeException('Apenas prestador.'),
                'iniciar' => $this->exigePrestador($role) ? $svc->iniciar($id, $uid) : throw new RuntimeException('Apenas prestador.'),
                'concluir' => $this->exigePrestador($role) ? $svc->concluir($id, $uid) : throw new RuntimeException('Apenas prestador.'),
                'confirmar' => $this->exigeCliente($role) ? $svc->confirmar(
                    $id,
                    $uid,
                    isset($json['nota']) ? (int) $json['nota'] : null,
                    $json['comentario'] ?? null
                ) : throw new RuntimeException('Apenas cliente.'),
                'cancelar' => $svc->cancelar($id, $uid, $role),
                default => throw new RuntimeException('Ação inválida.'),
            };

            $pag = model(PagamentoModel::class)->doAgendamento($id);

            return $this->jsonOk([
                'agendamento' => $result,
                'pagamento'   => $pag,
            ]);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    private function exigePrestador(string $role): bool
    {
        return in_array($role, ['prestador', 'admin'], true);
    }

    private function exigeCliente(string $role): bool
    {
        return in_array($role, ['cliente', 'admin'], true);
    }
}
