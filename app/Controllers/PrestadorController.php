<?php

namespace App\Controllers;

use App\Libraries\AgendamentoService;
use App\Models\AgendamentoModel;
use App\Models\PagamentoModel;
use App\Models\PerfilPrestadorModel;
use RuntimeException;

class PrestadorController extends BaseController
{
    public function solicitacoes(): string
    {
        $this->exigeRole('prestador', 'admin');
        $uid = $this->usuarioRole() === 'admin'
            ? (int) ($this->request->getGet('uid') ?: $this->usuarioId())
            : $this->usuarioId();

        return $this->renderPage('pages/prestador/solicitacoes', [
            'title'         => 'Solicitações',
            'activePage'    => 'solicitacoes',
            'agendamentos'  => model(AgendamentoModel::class)->doPrestador($uid, 'pendente'),
        ]);
    }

    public function servicos(): string
    {
        $this->exigeRole('prestador', 'admin');
        $uid = $this->usuarioId();

        return $this->renderPage('pages/prestador/servicos', [
            'title'        => 'Meus serviços',
            'activePage'   => 'meus-servicos',
            'agendamentos' => model(AgendamentoModel::class)->doPrestador($uid),
        ]);
    }

    public function perfil(): mixed
    {
        $this->exigeRole('prestador', 'admin');
        $perfil = model(PerfilPrestadorModel::class)->findByUsuario($this->usuarioId());

        return $this->renderPage('pages/prestador/perfil', [
            'title'      => 'Meu perfil de prestador',
            'activePage' => 'perfil-prestador',
            'perfil'     => $perfil,
            'tiposSel'   => model(PerfilPrestadorModel::class)->tiposComoArray($perfil['tipos_servico'] ?? ''),
        ]);
    }

    public function salvarPerfil(): mixed
    {
        $this->exigeRole('prestador', 'admin');

        try {
            $cfg   = config('Servicos');
            $tipos = $this->request->getPost('tipos') ?? [];
            if (! is_array($tipos) || $tipos === []) {
                return redirect()->back()->with('error', 'Selecione ao menos um tipo de serviço.');
            }

            $tipos = array_values(array_intersect($tipos, $cfg->chaves()));
            if ($tipos === []) {
                return redirect()->back()->with('error', 'Selecione ao menos um tipo de serviço válido.');
            }

            $money = static fn ($v) => (float) str_replace(',', '.', (string) $v);

            $data = [
                'tipos_servico'    => implode(',', $tipos),
                'bio'              => trim((string) $this->request->getPost('bio')),
                'valor_diaria'     => $money($this->request->getPost('valor_diaria')),
                'valor_passeio'    => $money($this->request->getPost('valor_passeio')),
                'valor_telhado'    => $money($this->request->getPost('valor_telhado')),
                'valor_piscina'    => $money($this->request->getPost('valor_piscina')),
                'valor_jardim'     => $money($this->request->getPost('valor_jardim')),
                'valor_hidraulico' => $money($this->request->getPost('valor_hidraulico')),
                'cidade'           => trim((string) $this->request->getPost('cidade')),
                'bairro'           => trim((string) $this->request->getPost('bairro')),
                'mp_email'         => strtolower(trim((string) $this->request->getPost('mp_email'))),
                'disponivel'       => $this->request->getPost('disponivel') ? 1 : 0,
            ];

            $model  = model(PerfilPrestadorModel::class);
            $perfil = $model->findByUsuario($this->usuarioId());

            if ($perfil) {
                $model->update($perfil['id'], $data);
            } else {
                $data['usuario_id'] = $this->usuarioId();
                $model->insert($data);
            }

            return redirect()->to(base_url('prestador/perfil'))->with('success', 'Perfil atualizado.');
        } catch (\Throwable $e) {
            log_message('error', 'Salvar perfil prestador: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->withInput()->with(
                'error',
                'Não foi possível salvar o perfil. Se o banco for antigo, rode upgrade-db.php. Detalhe: ' . $e->getMessage()
            );
        }
    }

    public function ganhos(): string
    {
        $this->exigeRole('prestador', 'admin');
        $uid = $this->usuarioId();
        $pg  = model(PagamentoModel::class);

        $lista = $pg->db->table('pagamentos pg')
            ->select('pg.*, a.data_servico, a.tipo_servico, a.status AS agendamento_status, c.nome AS cliente_nome')
            ->join('agendamentos a', 'a.id = pg.agendamento_id')
            ->join('usuarios c', 'c.id = a.cliente_id')
            ->where('a.prestador_id', $uid)
            ->orderBy('pg.id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->renderPage('pages/prestador/ganhos', [
            'title'      => 'Ganhos',
            'activePage' => 'ganhos',
            'resumo'     => $pg->ganhosPrestador($uid),
            'lista'      => $lista,
        ]);
    }

    public function aceitar(int $id): mixed
    {
        $this->exigeRole('prestador', 'admin');

        try {
            (new AgendamentoService())->aceitar($id, $this->usuarioId());
            $this->flashOk('Solicitação aceita. Combine os detalhes com o cliente.');
        } catch (RuntimeException $e) {
            $this->flashErro($e->getMessage());
        }

        return redirect()->to(base_url('prestador/solicitacoes'));
    }

    public function rejeitar(int $id): mixed
    {
        $this->exigeRole('prestador', 'admin');

        try {
            (new AgendamentoService())->rejeitar(
                $id,
                $this->usuarioId(),
                trim((string) $this->request->getPost('motivo'))
            );
            $this->flashOk('Solicitação rejeitada. O valor reservado foi estornado.');
        } catch (RuntimeException $e) {
            $this->flashErro($e->getMessage());
        }

        return redirect()->to(base_url('prestador/solicitacoes'));
    }

    public function iniciar(int $id): mixed
    {
        $this->exigeRole('prestador', 'admin');

        try {
            (new AgendamentoService())->iniciar($id, $this->usuarioId());
            $this->flashOk('Serviço iniciado.');
        } catch (RuntimeException $e) {
            $this->flashErro($e->getMessage());
        }

        return redirect()->to(base_url('agendamentos/' . $id));
    }

    public function concluir(int $id): mixed
    {
        $this->exigeRole('prestador', 'admin');

        try {
            (new AgendamentoService())->concluir($id, $this->usuarioId());
            $this->flashOk('Serviço marcado como concluído. Aguardando confirmação do cliente.');
        } catch (RuntimeException $e) {
            $this->flashErro($e->getMessage());
        }

        return redirect()->to(base_url('agendamentos/' . $id));
    }
}
