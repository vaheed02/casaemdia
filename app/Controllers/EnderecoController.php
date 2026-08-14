<?php

namespace App\Controllers;

use App\Models\EnderecoModel;

class EnderecoController extends BaseController
{
    public function index(): string
    {
        $this->exigeRole('cliente', 'admin');

        return $this->renderPage('pages/enderecos/index', [
            'title'      => 'Meus endereços',
            'activePage' => 'enderecos',
            'enderecos'  => model(EnderecoModel::class)->doUsuario($this->usuarioId()),
        ]);
    }

    public function salvar(): mixed
    {
        $this->exigeRole('cliente', 'admin');

        $data = [
            'usuario_id'  => $this->usuarioId(),
            'titulo'      => trim((string) $this->request->getPost('titulo')) ?: 'Casa',
            'cep'         => trim((string) $this->request->getPost('cep')),
            'logradouro'  => trim((string) $this->request->getPost('logradouro')),
            'numero'      => trim((string) $this->request->getPost('numero')),
            'complemento' => trim((string) $this->request->getPost('complemento')),
            'bairro'      => trim((string) $this->request->getPost('bairro')),
            'cidade'      => trim((string) $this->request->getPost('cidade')),
            'uf'          => strtoupper(trim((string) $this->request->getPost('uf'))),
            'principal'   => $this->request->getPost('principal') ? 1 : 0,
        ];

        if ($data['logradouro'] === '' || $data['bairro'] === '' || $data['cidade'] === '' || strlen($data['uf']) !== 2) {
            return redirect()->back()->withInput()->with('error', 'Preencha logradouro, bairro, cidade e UF.');
        }

        $model = model(EnderecoModel::class);
        if ($data['principal']) {
            $model->where('usuario_id', $this->usuarioId())->set(['principal' => 0])->update();
        }

        $model->insert($data);

        return redirect()->to(base_url('enderecos'))->with('success', 'Endereço cadastrado.');
    }

    public function excluir(int $id): mixed
    {
        $this->exigeRole('cliente', 'admin');
        $model = model(EnderecoModel::class);
        $end   = $model->find($id);

        if ($end && (int) $end['usuario_id'] === $this->usuarioId()) {
            $model->delete($id);
            $this->flashOk('Endereço removido.');
        }

        return redirect()->to(base_url('enderecos'));
    }
}
