<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nome', 'email', 'senha', 'telefone', 'role', 'ativo',
        'reset_token', 'token_expira',
    ];

    public function findAtivoByEmail(string $email): ?array
    {
        return $this->where('email', $email)
            ->where('ativo', 1)
            ->first();
    }

    public function listarPorRole(?string $role = null): array
    {
        $builder = $this->orderBy('nome', 'ASC');
        if ($role !== null && $role !== '') {
            $builder->where('role', $role);
        }

        return $builder->findAll();
    }
}
