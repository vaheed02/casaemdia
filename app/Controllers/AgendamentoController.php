<?php

namespace App\Controllers;

use App\Libraries\AgendamentoService;
use App\Models\AgendamentoModel;
use App\Models\ConfiguracaoModel;
use App\Models\EnderecoModel;
use App\Models\PagamentoModel;
use App\Models\PerfilPrestadorModel;
use App\Models\UsuarioModel;
use RuntimeException;

class AgendamentoController extends BaseController
{
    public function index(): string
    {
        $role = $this->usuarioRole();
        $uid  = $this->usuarioId();
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

        return $this->renderPage('pages/agendamentos/index', [
            'title'        => 'Agendamentos',
            'activePage'   => 'meus-agendamentos',
            'agendamentos' => $lista,
        ]);
    }

    public function novo(): mixed
    {
        $this->exigeRole('cliente', 'admin');

        $cfgTipos    = config('Servicos');
        $prestadorId = (int) $this->request->getGet('prestador');
        $tipo        = (string) ($this->request->getGet('tipo') ?: 'diarista');
        if (! $cfgTipos->ehTipoValido($tipo)) {
            $tipo = 'diarista';
        }
        $prestadores = model(PerfilPrestadorModel::class)->listarDisponiveis();
        $enderecos   = model(EnderecoModel::class)->doUsuario($this->usuarioId());
        $valor       = 0;

        if ($prestadorId > 0) {
            $valor = (new AgendamentoService())->valorSugerido($prestadorId, $tipo);
        }

        return $this->renderPage('pages/agendamentos/novo', [
            'title'        => 'Novo agendamento',
            'activePage'   => 'agendar',
            'prestadores'  => $prestadores,
            'enderecos'    => $enderecos,
            'prestadorId'  => $prestadorId,
            'tipo'         => $tipo,
            'valorSugerido'=> $valor,
            'tiposCfg'     => $cfgTipos->tipos,
        ]);
    }

    public function criar(): mixed
    {
        $this->exigeRole('cliente', 'admin');

        $cfgTipos    = config('Servicos');
        $prestadorId = (int) $this->request->getPost('prestador_id');
        $tipo        = (string) $this->request->getPost('tipo_servico');
        $enderecoId  = (int) $this->request->getPost('endereco_id');
        $data        = (string) $this->request->getPost('data_servico');
        $hora        = (string) $this->request->getPost('hora_inicio');
        $duracao     = (float) $this->request->getPost('duracao_horas');
        $obs         = trim((string) $this->request->getPost('observacoes_cliente'));

        if (! $cfgTipos->ehTipoValido($tipo)) {
            return redirect()->back()->withInput()->with('error', 'Tipo de serviço inválido.');
        }

        $prestador = model(UsuarioModel::class)->find($prestadorId);
        if ($prestador === null || $prestador['role'] !== 'prestador') {
            return redirect()->back()->withInput()->with('error', 'Prestador inválido.');
        }

        $endereco = model(EnderecoModel::class)->find($enderecoId);
        if ($endereco === null || (int) $endereco['usuario_id'] !== $this->usuarioId()) {
            return redirect()->back()->withInput()->with('error', 'Selecione um endereço válido. Cadastre um em Meus endereços.');
        }

        if ($data === '' || $hora === '') {
            return redirect()->back()->withInput()->with('error', 'Informe data e horário.');
        }

        $svc   = new AgendamentoService();
        $valor = $svc->valorSugerido($prestadorId, $tipo);

        // Valor personalizado (ex.: teste com R$ 5,00)
        $valorCustom = trim((string) $this->request->getPost('valor_total'));
        if ($valorCustom !== '') {
            $raw = preg_replace('/[^\d,.\-]/', '', $valorCustom) ?? '';
            if (str_contains($raw, ',') && str_contains($raw, '.')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } elseif (str_contains($raw, ',')) {
                $raw = str_replace(',', '.', $raw);
            }
            $valor = (float) $raw;
        }

        if ($valor < 1) {
            return redirect()->back()->withInput()->with('error', 'Informe um valor válido (mínimo R$ 1,00). Prestador sem preço ou valor inválido.');
        }

        try {
            $ag = $svc->criar([
                'cliente_id'          => $this->usuarioId(),
                'prestador_id'        => $prestadorId,
                'tipo_servico'        => $tipo,
                'endereco_id'         => $enderecoId,
                'data_servico'        => $data,
                'hora_inicio'         => $hora,
                'duracao_horas'       => $duracao > 0 ? $duracao : $cfgTipos->duracaoPadrao($tipo),
                'valor_total'         => $valor,
                'observacoes_cliente' => $obs,
            ]);

            // Sempre leva o cliente ao checkout (pagar agora)
            return redirect()
                ->to(base_url('pagamentos/checkout/' . $ag['id']))
                ->with('success', 'Agendamento #' . $ag['id'] . ' criado. Conclua o pagamento de ' .
                    'R$ ' . number_format($valor, 2, ',', '.') . ' para o prestador poder aceitar.');
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id): mixed
    {
        $ag = model(AgendamentoModel::class)->comRelacoes($id);
        if ($ag === null) {
            return redirect()->to(base_url('agendamentos'))->with('error', 'Agendamento não encontrado.');
        }

        $role = $this->usuarioRole();
        $uid  = $this->usuarioId();
        $ok   = $role === 'admin'
            || (int) $ag['cliente_id'] === $uid
            || (int) $ag['prestador_id'] === $uid;

        if (! $ok) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acesso negado.');
        }

        return $this->renderPage('pages/agendamentos/show', [
            'title'      => 'Agendamento #' . $id,
            'activePage' => 'meus-agendamentos',
            'ag'         => $ag,
            'pagamento'  => model(PagamentoModel::class)->doAgendamento($id),
        ]);
    }

    public function confirmar(int $id): mixed
    {
        $this->exigeRole('cliente', 'admin');

        try {
            $nota = $this->request->getPost('nota');
            (new AgendamentoService())->confirmar(
                $id,
                $this->usuarioId(),
                $nota !== null && $nota !== '' ? (int) $nota : null,
                trim((string) $this->request->getPost('comentario')) ?: null,
            );
            $this->flashOk('Serviço confirmado! Comissão retida pela plataforma; líquido do prestador aguarda repasse (admin).');
        } catch (RuntimeException $e) {
            $this->flashErro($e->getMessage());
        }

        return redirect()->to(base_url('agendamentos/' . $id));
    }

    public function cancelar(int $id): mixed
    {
        try {
            (new AgendamentoService())->cancelar($id, $this->usuarioId(), $this->usuarioRole());
            $this->flashOk('Agendamento cancelado e reserva estornada.');
        } catch (RuntimeException $e) {
            $this->flashErro($e->getMessage());
        }

        return redirect()->to(base_url('agendamentos/' . $id));
    }
}
