<?php

namespace App\Filters;

use App\Libraries\ApiToken;
use App\Models\UsuarioModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        $token  = '';

        if (preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
            $token = $m[1];
        }

        if ($token === '') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Token ausente. Envie Authorization: Bearer <jwt>']);
        }

        try {
            $payload = ApiToken::decode($token);
        } catch (\Throwable $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Token inválido ou expirado']);
        }

        $uid = (int) ($payload['id'] ?? $payload['sub'] ?? 0);
        $user = model(UsuarioModel::class)->find($uid);

        if ($user === null || ! (int) ($user['ativo'] ?? 0)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Usuário inativo ou não encontrado']);
        }

        // Disponibiliza no request e na sessão (helpers existentes)
        $request->apiUser = $user;
        session()->set([
            'id'    => $user['id'],
            'nome'  => $user['nome'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'api'   => true,
        ]);

        if ($arguments) {
            $roles = is_array($arguments) ? $arguments : [$arguments];
            if (! in_array($user['role'], $roles, true) && $user['role'] !== 'admin') {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['ok' => false, 'error' => 'Sem permissão para este recurso']);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
