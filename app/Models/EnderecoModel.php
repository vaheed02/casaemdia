<?php

namespace App\Models;

use CodeIgniter\Model;

class EnderecoModel extends Model
{
    protected $table         = 'enderecos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'usuario_id', 'titulo', 'cep', 'logradouro', 'numero',
        'complemento', 'bairro', 'cidade', 'uf', 'principal',
    ];

    public function doUsuario(int $usuarioId): array
    {
        return $this->where('usuario_id', $usuarioId)
            ->orderBy('principal', 'DESC')
            ->orderBy('titulo', 'ASC')
            ->findAll();
    }

    public function formatarLinha(array $e): string
    {
        $parts = [
            $e['logradouro'] ?? '',
            $e['numero'] ?? '',
            $e['bairro'] ?? '',
            ($e['cidade'] ?? '') . '/' . ($e['uf'] ?? ''),
        ];

        return implode(', ', array_filter($parts, static fn ($v) => $v !== '' && $v !== '/'));
    }
}
