<?php

namespace App\Controllers\Api;

use App\Libraries\ApiToken;
use App\Models\PerfilPrestadorModel;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthApiController extends BaseApiController
{
    public function login(): ResponseInterface
    {
        $json  = $this->request->getJSON(true) ?? [];
        $email = strtolower(trim((string) ($json['email'] ?? $this->request->getPost('email') ?? '')));
        $senha = (string) ($json['senha'] ?? $json['password'] ?? $this->request->getPost('senha') ?? '');

        if ($email === '' || $senha === '') {
            return $this->jsonError('Informe e-mail e senha.', 422);
        }

        $user = model(UsuarioModel::class)->findAtivoByEmail($email);
        if ($user === null || ! password_verify($senha, $user['senha'])) {
            return $this->jsonError('E-mail ou senha inválidos.', 401);
        }

        if (! in_array($user['role'], ['cliente', 'prestador', 'admin'], true)) {
            return $this->jsonError('Perfil não suportado no app.', 403);
        }

        $token = ApiToken::issue($user);

        return $this->jsonOk([
            'token'   => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24 * 30,
            'usuario' => $this->jsonUsuario($user),
        ]);
    }

    public function register(): ResponseInterface
    {
        $json = $this->request->getJSON(true) ?? [];
        $nome = trim((string) ($json['nome'] ?? ''));
        $email = strtolower(trim((string) ($json['email'] ?? '')));
        $senha = (string) ($json['senha'] ?? $json['password'] ?? '');
        $telefone = trim((string) ($json['telefone'] ?? ''));
        $role = (string) ($json['role'] ?? 'cliente');

        if (! in_array($role, ['cliente', 'prestador'], true)) {
            return $this->jsonError('Role deve ser cliente ou prestador.', 422);
        }
        if (strlen($nome) < 3 || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            return $this->jsonError('Dados inválidos. Nome (3+), e-mail válido e senha (6+).', 422);
        }

        $model = model(UsuarioModel::class);
        if ($model->where('email', $email)->first()) {
            return $this->jsonError('E-mail já cadastrado.', 409);
        }

        $id = $model->insert([
            'nome'     => $nome,
            'email'    => $email,
            'senha'    => password_hash($senha, PASSWORD_DEFAULT),
            'telefone' => $telefone,
            'role'     => $role,
            'ativo'    => 1,
        ]);

        if ($role === 'prestador') {
            model(PerfilPrestadorModel::class)->insert([
                'usuario_id'       => $id,
                'tipos_servico'    => 'diarista',
                'bio'              => '',
                'valor_diaria'     => 150,
                'valor_passeio'    => 40,
                'valor_telhado'    => 0,
                'valor_piscina'    => 0,
                'valor_jardim'     => 0,
                'valor_hidraulico' => 0,
                'cidade'           => '',
                'bairro'           => '',
                'disponivel'       => 1,
            ]);
        }

        $user  = $model->find($id);
        $token = ApiToken::issue($user);

        return $this->jsonOk([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24 * 30,
            'usuario'    => $this->jsonUsuario($user),
        ], 201);
    }

    public function me(): ResponseInterface
    {
        $user = $this->apiUser();
        if (! $user) {
            return $this->jsonError('Não autenticado', 401);
        }

        $full = model(UsuarioModel::class)->find((int) $user['id']);
        $data = $this->jsonUsuario($full ?: $user);

        if (($data['role'] ?? '') === 'prestador') {
            $perfil = model(PerfilPrestadorModel::class)->findByUsuario((int) $data['id']);
            $data['perfil'] = $perfil;
        }

        return $this->jsonOk($data);
    }
}
