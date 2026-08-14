<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use App\Models\PagamentoModel;

class Home extends BaseController
{
    public function index(): string
    {
        $role = $this->usuarioRole();
        $uid  = $this->usuarioId();
        $ag   = model(AgendamentoModel::class);

        $data = [
            'title'      => 'Painel',
            'activePage' => 'dashboard',
        ];

        if ($role === 'cliente') {
            $data['contagens']     = $ag->contagemPorStatus('cliente_id', $uid);
            $data['agendamentos']  = array_slice($ag->doCliente($uid), 0, 5);
        } elseif ($role === 'prestador') {
            $data['contagens']     = $ag->contagemPorStatus('prestador_id', $uid);
            $data['agendamentos']  = array_slice($ag->doPrestador($uid), 0, 5);
            $data['ganhos']        = model(PagamentoModel::class)->ganhosPrestador($uid);
            $data['solicitacoes']  = $ag->doPrestador($uid, 'pendente');
        } else {
            $data['contagens']  = $this->contagensAdmin();
            $data['totais']     = model(PagamentoModel::class)->totaisPlataforma();
            $data['pagamentos'] = array_slice(model(PagamentoModel::class)->listarComDetalhes(), 0, 8);
        }

        return $this->renderPage('pages/dashboard', $data);
    }

    private function contagensAdmin(): array
    {
        $db = db_connect();
        $porStatus = $db->table('agendamentos')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $out = ['usuarios' => $db->table('usuarios')->countAllResults()];
        foreach ($porStatus as $row) {
            $out[$row['status']] = (int) $row['total'];
        }

        return $out;
    }
}
