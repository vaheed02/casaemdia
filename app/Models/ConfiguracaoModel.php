<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfiguracaoModel extends Model
{
    protected $table         = 'configuracoes';
    protected $primaryKey    = 'chave';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['chave', 'valor', 'updated_at'];

    /**
     * Lê valor de configuração (não usar get/set — conflitam com CodeIgniter\Model).
     */
    public function valorDe(string $chave, ?string $default = null): ?string
    {
        $row = $this->find($chave);

        return $row['valor'] ?? $default;
    }

    public function salvarValor(string $chave, string $valor): void
    {
        $data = [
            'chave'      => $chave,
            'valor'      => $valor,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->find($chave)) {
            $this->update($chave, $data);
        } else {
            $this->insert($data);
        }
    }

    public function comissaoPercentual(): float
    {
        $v = $this->valorDe('comissao_percentual');
        if ($v === null || $v === '') {
            return (float) config('Servicos')->comissaoPercentual;
        }

        return (float) str_replace(',', '.', $v);
    }
}
