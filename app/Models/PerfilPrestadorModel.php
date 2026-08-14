<?php

namespace App\Models;

use CodeIgniter\Model;

class PerfilPrestadorModel extends Model
{
    protected $table         = 'perfis_prestador';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'usuario_id', 'tipos_servico', 'bio',
        'valor_diaria', 'valor_passeio', 'valor_telhado', 'valor_piscina',
        'valor_jardim', 'valor_hidraulico',
        'cidade', 'bairro', 'mp_email',
        'avaliacao_media', 'total_avaliacoes', 'disponivel',
    ];

    public function findByUsuario(int $usuarioId): ?array
    {
        return $this->where('usuario_id', $usuarioId)->first();
    }

    /**
     * Lista prestadores disponíveis com dados do usuário.
     */
    public function listarDisponiveis(?string $tipo = null, ?string $cidade = null): array
    {
        $builder = $this->db->table('perfis_prestador p')
            ->select('p.*, u.nome, u.email, u.telefone')
            ->join('usuarios u', 'u.id = p.usuario_id')
            ->where('p.disponivel', 1)
            ->where('u.ativo', 1)
            ->where('u.role', 'prestador');

        if ($tipo !== null && $tipo !== '') {
            // Match exato no CSV (evita falso positivo entre tipos)
            $builder->where(
                'FIND_IN_SET(' . $this->db->escape($tipo) . ", REPLACE(p.tipos_servico, ' ', '')) > 0",
                null,
                false
            );
        }

        if ($cidade !== null && $cidade !== '') {
            $builder->like('p.cidade', $cidade);
        }

        return $builder->orderBy('p.avaliacao_media', 'DESC')
            ->orderBy('u.nome', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function tiposComoArray(?string $csv): array
    {
        if ($csv === null || trim($csv) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $csv))));
    }

    /**
     * Valor configurado no perfil para um tipo de serviço.
     */
    public function valorDoTipo(array $perfil, string $tipo): float
    {
        $campo = config('Servicos')->campoValor($tipo);

        return (float) ($perfil[$campo] ?? 0);
    }
}
