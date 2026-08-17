<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseApiController extends BaseController
{
    protected function apiUser(): ?array
    {
        return $this->request->apiUser
            ?? (session('id') ? [
                'id'    => session('id'),
                'nome'  => session('nome'),
                'email' => session('email'),
                'role'  => session('role'),
            ] : null);
    }

    protected function jsonOk(mixed $data = null, int $code = 200, array $extra = []): ResponseInterface
    {
        $body = array_merge(['ok' => true], $extra);
        if ($data !== null) {
            $body['data'] = $data;
        }

        return $this->response->setStatusCode($code)->setJSON($body);
    }

    protected function jsonError(string $message, int $code = 400, array $extra = []): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON(array_merge([
            'ok'    => false,
            'error' => $message,
        ], $extra));
    }

    protected function jsonUsuario(array $u): array
    {
        return [
            'id'       => (int) $u['id'],
            'nome'     => $u['nome'],
            'email'    => $u['email'],
            'telefone' => $u['telefone'] ?? null,
            'role'     => $u['role'],
            'ativo'    => (int) ($u['ativo'] ?? 1),
        ];
    }
}
