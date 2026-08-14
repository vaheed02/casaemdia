<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendamentoModel extends Model
{
    protected $table         = 'agendamentos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'cliente_id', 'prestador_id', 'tipo_servico', 'endereco_id',
        'data_servico', 'hora_inicio', 'duracao_horas',
        'valor_total', 'comissao_percentual', 'comissao_valor', 'valor_prestador',
        'status', 'observacoes_cliente', 'motivo_rejeicao',
        'aceito_em', 'iniciado_em', 'concluido_em', 'confirmado_em', 'pago_em',
    ];

    public function comRelacoes(int $id): ?array
    {
        return $this->db->table('agendamentos a')
            ->select('a.*, c.nome AS cliente_nome, c.email AS cliente_email, c.telefone AS cliente_telefone,
                      p.nome AS prestador_nome, p.email AS prestador_email, p.telefone AS prestador_telefone,
                      e.titulo AS endereco_titulo, e.logradouro, e.numero, e.bairro, e.cidade, e.uf, e.cep')
            ->join('usuarios c', 'c.id = a.cliente_id')
            ->join('usuarios p', 'p.id = a.prestador_id')
            ->join('enderecos e', 'e.id = a.endereco_id', 'left')
            ->where('a.id', $id)
            ->get()
            ->getRowArray();
    }

    public function doCliente(int $clienteId, ?string $status = null): array
    {
        $builder = $this->db->table('agendamentos a')
            ->select('a.*, p.nome AS prestador_nome')
            ->join('usuarios p', 'p.id = a.prestador_id')
            ->where('a.cliente_id', $clienteId);

        if ($status) {
            $builder->where('a.status', $status);
        }

        return $builder->orderBy('a.data_servico', 'DESC')
            ->orderBy('a.hora_inicio', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function doPrestador(int $prestadorId, ?string $status = null): array
    {
        $builder = $this->db->table('agendamentos a')
            ->select('a.*, c.nome AS cliente_nome')
            ->join('usuarios c', 'c.id = a.cliente_id')
            ->where('a.prestador_id', $prestadorId);

        if ($status) {
            $builder->where('a.status', $status);
        }

        return $builder->orderBy('a.data_servico', 'DESC')
            ->orderBy('a.hora_inicio', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function contagemPorStatus(string $campoUsuario, int $usuarioId): array
    {
        $rows = $this->db->table('agendamentos')
            ->select('status, COUNT(*) AS total')
            ->where($campoUsuario, $usuarioId)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['total'];
        }

        return $out;
    }

    public function calcularValores(float $valorTotal, float $comissaoPercentual): array
    {
        $comissao = round($valorTotal * ($comissaoPercentual / 100), 2);
        $liquido  = round($valorTotal - $comissao, 2);

        return [
            'comissao_valor'  => $comissao,
            'valor_prestador' => $liquido,
        ];
    }
}
