<?php

namespace App\Models;

use CodeIgniter\Model;

class AvaliacaoModel extends Model
{
    protected $table         = 'avaliacoes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'agendamento_id', 'cliente_id', 'prestador_id', 'nota', 'comentario',
    ];

    public function doAgendamento(int $agendamentoId): ?array
    {
        return $this->where('agendamento_id', $agendamentoId)->first();
    }

    public function recalcularMediaPrestador(int $prestadorId): void
    {
        $row = $this->db->table('avaliacoes')
            ->select('AVG(nota) AS media, COUNT(*) AS total')
            ->where('prestador_id', $prestadorId)
            ->get()
            ->getRowArray();

        $media = round((float) ($row['media'] ?? 0), 2);
        $total = (int) ($row['total'] ?? 0);

        model(PerfilPrestadorModel::class)
            ->where('usuario_id', $prestadorId)
            ->set([
                'avaliacao_media'  => $media,
                'total_avaliacoes' => $total,
            ])
            ->update();
    }
}
