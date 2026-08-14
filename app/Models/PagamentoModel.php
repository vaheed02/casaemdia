<?php

namespace App\Models;

use CodeIgniter\Model;

class PagamentoModel extends Model
{
    protected $table         = 'pagamentos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'agendamento_id', 'gateway', 'gateway_ref', 'status',
        'valor_bruto', 'valor_comissao', 'valor_liquido_prestador',
        'autorizado_em', 'capturado_em',
        'mp_preference_id', 'mp_payment_id', 'mp_status', 'checkout_url',
        'payout_status', 'payout_ref', 'payout_em', 'payout_nota', 'meta_json',
    ];

    public function doAgendamento(int $agendamentoId): ?array
    {
        return $this->where('agendamento_id', $agendamentoId)->first();
    }

    public function listarComDetalhes(?string $status = null): array
    {
        $builder = $this->db->table('pagamentos pg')
            ->select('pg.*, a.data_servico, a.tipo_servico, a.status AS agendamento_status,
                      c.nome AS cliente_nome, p.nome AS prestador_nome')
            ->join('agendamentos a', 'a.id = pg.agendamento_id')
            ->join('usuarios c', 'c.id = a.cliente_id')
            ->join('usuarios p', 'p.id = a.prestador_id')
            ->orderBy('pg.id', 'DESC');

        if ($status) {
            $builder->where('pg.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    public function totaisPlataforma(): array
    {
        $row = $this->db->table('pagamentos')
            ->select('COALESCE(SUM(valor_bruto),0) AS bruto,
                      COALESCE(SUM(valor_comissao),0) AS comissao,
                      COALESCE(SUM(valor_liquido_prestador),0) AS liquido,
                      COUNT(*) AS qtd')
            ->where('status', 'capturado')
            ->get()
            ->getRowArray();

        $repasses = $this->db->table('pagamentos')
            ->select("COALESCE(SUM(CASE WHEN payout_status='pendente' THEN valor_liquido_prestador ELSE 0 END),0) AS a_repassar,
                      COALESCE(SUM(CASE WHEN payout_status='pago' THEN valor_liquido_prestador ELSE 0 END),0) AS repassado,
                      COALESCE(SUM(CASE WHEN status='autorizado' THEN valor_bruto ELSE 0 END),0) AS retido_aguardando_servico")
            ->get()
            ->getRowArray();

        return array_merge(
            $row ?: ['bruto' => 0, 'comissao' => 0, 'liquido' => 0, 'qtd' => 0],
            $repasses ?: ['a_repassar' => 0, 'repassado' => 0, 'retido_aguardando_servico' => 0]
        );
    }

    public function aRepassar(): array
    {
        return $this->db->table('pagamentos pg')
            ->select('pg.*, a.data_servico, a.tipo_servico, c.nome AS cliente_nome, p.nome AS prestador_nome, pf.mp_email')
            ->join('agendamentos a', 'a.id = pg.agendamento_id')
            ->join('usuarios c', 'c.id = a.cliente_id')
            ->join('usuarios p', 'p.id = a.prestador_id')
            ->join('perfis_prestador pf', 'pf.usuario_id = a.prestador_id', 'left')
            ->where('pg.status', 'capturado')
            ->where('pg.payout_status', 'pendente')
            ->orderBy('pg.capturado_em', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function ganhosPrestador(int $prestadorId): array
    {
        $row = $this->db->table('pagamentos pg')
            ->select('COALESCE(SUM(pg.valor_liquido_prestador),0) AS total_recebido,
                      COALESCE(SUM(pg.valor_comissao),0) AS total_comissao,
                      COUNT(*) AS qtd')
            ->join('agendamentos a', 'a.id = pg.agendamento_id')
            ->where('a.prestador_id', $prestadorId)
            ->where('pg.status', 'capturado')
            ->get()
            ->getRowArray();

        return $row ?: ['total_recebido' => 0, 'total_comissao' => 0, 'qtd' => 0];
    }
}
