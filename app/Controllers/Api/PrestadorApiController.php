<?php

namespace App\Controllers\Api;

use App\Models\AgendamentoModel;
use App\Models\PagamentoModel;
use App\Models\PerfilPrestadorModel;
use CodeIgniter\HTTP\ResponseInterface;

class PrestadorApiController extends BaseApiController
{
    public function solicitacoes(): ResponseInterface
    {
        $uid = (int) $this->apiUser()['id'];

        return $this->jsonOk(model(AgendamentoModel::class)->doPrestador($uid, 'pendente'));
    }

    public function servicos(): ResponseInterface
    {
        $uid = (int) $this->apiUser()['id'];

        return $this->jsonOk(model(AgendamentoModel::class)->doPrestador($uid));
    }

    public function ganhos(): ResponseInterface
    {
        $uid = (int) $this->apiUser()['id'];
        $pg  = model(PagamentoModel::class);

        $lista = $pg->db->table('pagamentos pg')
            ->select('pg.*, a.data_servico, a.tipo_servico, c.nome AS cliente_nome')
            ->join('agendamentos a', 'a.id = pg.agendamento_id')
            ->join('usuarios c', 'c.id = a.cliente_id')
            ->where('a.prestador_id', $uid)
            ->orderBy('pg.id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->jsonOk([
            'resumo' => $pg->ganhosPrestador($uid),
            'lista'  => $lista,
        ]);
    }

    public function perfil(): ResponseInterface
    {
        $uid    = (int) $this->apiUser()['id'];
        $perfil = model(PerfilPrestadorModel::class)->findByUsuario($uid);

        return $this->jsonOk($perfil);
    }

    public function salvarPerfil(): ResponseInterface
    {
        $uid   = (int) $this->apiUser()['id'];
        $json  = $this->request->getJSON(true) ?? [];
        $tipos = $json['tipos'] ?? $json['tipos_servico'] ?? [];

        if (is_string($tipos)) {
            $tipos = array_filter(array_map('trim', explode(',', $tipos)));
        }
        if (! is_array($tipos) || $tipos === []) {
            return $this->jsonError('Selecione ao menos um tipo de serviço.', 422);
        }
        $tipos = array_values(array_intersect($tipos, [
            'diarista', 'passeador', 'telhado', 'piscinas', 'jardins', 'hidraulico',
        ]));
        if ($tipos === []) {
            return $this->jsonError('Tipos de serviço inválidos.', 422);
        }

        $data = [
            'tipos_servico'    => implode(',', $tipos),
            'bio'              => trim((string) ($json['bio'] ?? '')),
            'valor_diaria'     => (float) ($json['valor_diaria'] ?? 0),
            'valor_passeio'    => (float) ($json['valor_passeio'] ?? 0),
            'valor_telhado'    => (float) ($json['valor_telhado'] ?? 0),
            'valor_piscina'    => (float) ($json['valor_piscina'] ?? 0),
            'valor_jardim'     => (float) ($json['valor_jardim'] ?? 0),
            'valor_hidraulico' => (float) ($json['valor_hidraulico'] ?? 0),
            'cidade'           => trim((string) ($json['cidade'] ?? '')),
            'bairro'           => trim((string) ($json['bairro'] ?? '')),
            'disponivel'       => isset($json['disponivel']) ? ((int) (bool) $json['disponivel']) : 1,
        ];

        $model  = model(PerfilPrestadorModel::class);
        $perfil = $model->findByUsuario($uid);
        if ($perfil) {
            $model->update($perfil['id'], $data);
        } else {
            $data['usuario_id'] = $uid;
            $model->insert($data);
        }

        return $this->jsonOk($model->findByUsuario($uid));
    }
}
