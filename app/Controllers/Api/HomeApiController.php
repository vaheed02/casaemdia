<?php

namespace App\Controllers\Api;

use App\Models\AgendamentoModel;
use App\Models\PagamentoModel;
use CodeIgniter\HTTP\ResponseInterface;

class HomeApiController extends BaseApiController
{
    public function dashboard(): ResponseInterface
    {
        $user = $this->apiUser();
        $uid  = (int) $user['id'];
        $role = (string) $user['role'];
        $ag   = model(AgendamentoModel::class);

        if ($role === 'cliente') {
            return $this->jsonOk([
                'role'         => $role,
                'contagens'    => $ag->contagemPorStatus('cliente_id', $uid),
                'agendamentos' => array_slice($ag->doCliente($uid), 0, 10),
            ]);
        }

        if ($role === 'prestador') {
            return $this->jsonOk([
                'role'          => $role,
                'contagens'     => $ag->contagemPorStatus('prestador_id', $uid),
                'solicitacoes'  => $ag->doPrestador($uid, 'pendente'),
                'agendamentos'  => array_slice($ag->doPrestador($uid), 0, 10),
                'ganhos'        => model(PagamentoModel::class)->ganhosPrestador($uid),
            ]);
        }

        // admin
        return $this->jsonOk([
            'role'   => $role,
            'totais' => model(PagamentoModel::class)->totaisPlataforma(),
            'a_repassar' => model(PagamentoModel::class)->aRepassar(),
        ]);
    }
}
