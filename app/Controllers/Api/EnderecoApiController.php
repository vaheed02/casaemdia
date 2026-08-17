<?php

namespace App\Controllers\Api;

use App\Models\EnderecoModel;
use CodeIgniter\HTTP\ResponseInterface;

class EnderecoApiController extends BaseApiController
{
    public function index(): ResponseInterface
    {
        $uid = (int) $this->apiUser()['id'];

        return $this->jsonOk(model(EnderecoModel::class)->doUsuario($uid));
    }

    public function create(): ResponseInterface
    {
        $uid  = (int) $this->apiUser()['id'];
        $json = $this->request->getJSON(true) ?? [];

        $data = [
            'usuario_id'  => $uid,
            'titulo'      => trim((string) ($json['titulo'] ?? 'Casa')) ?: 'Casa',
            'cep'         => trim((string) ($json['cep'] ?? '')),
            'logradouro'  => trim((string) ($json['logradouro'] ?? '')),
            'numero'      => trim((string) ($json['numero'] ?? '')),
            'complemento' => trim((string) ($json['complemento'] ?? '')),
            'bairro'      => trim((string) ($json['bairro'] ?? '')),
            'cidade'      => trim((string) ($json['cidade'] ?? '')),
            'uf'          => strtoupper(trim((string) ($json['uf'] ?? ''))),
            'principal'   => ! empty($json['principal']) ? 1 : 0,
        ];

        if ($data['logradouro'] === '' || $data['bairro'] === '' || $data['cidade'] === '' || strlen($data['uf']) !== 2) {
            return $this->jsonError('Preencha logradouro, bairro, cidade e UF.', 422);
        }

        $model = model(EnderecoModel::class);
        if ($data['principal']) {
            $model->where('usuario_id', $uid)->set(['principal' => 0])->update();
        }

        $id = $model->insert($data);

        return $this->jsonOk($model->find($id), 201);
    }

    public function delete(int $id): ResponseInterface
    {
        $uid   = (int) $this->apiUser()['id'];
        $model = model(EnderecoModel::class);
        $end   = $model->find($id);

        if (! $end || (int) $end['usuario_id'] !== $uid) {
            return $this->jsonError('Endereço não encontrado.', 404);
        }

        $model->delete($id);

        return $this->jsonOk(['deleted' => true]);
    }
}
