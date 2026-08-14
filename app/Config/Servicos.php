<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Regras de negócio do marketplace de serviços.
 */
class Servicos extends BaseConfig
{
    /** Comissão padrão da plataforma (%) */
    public float $comissaoPercentual = 15.0;

    /**
     * Tipos de serviço oferecidos.
     * campo_valor = coluna em perfis_prestador
     * duracao_padrao = horas sugeridas no agendamento
     * icone = emoji para UI
     *
     * @var array<string, array{label:string,descricao:string,unidade:string,campo_valor:string,duracao_padrao:float,icone:string,categoria:string}>
     */
    public array $tipos = [
        'diarista' => [
            'label'           => 'Diarista',
            'descricao'       => 'Limpeza e organização residencial',
            'unidade'         => 'diária',
            'campo_valor'     => 'valor_diaria',
            'duracao_padrao'  => 4.0,
            'icone'           => '🏠',
            'categoria'       => 'Casa',
        ],
        'passeador' => [
            'label'           => 'Passeador de pets',
            'descricao'       => 'Passeio e cuidado com cães e pets',
            'unidade'         => 'passeio',
            'campo_valor'     => 'valor_passeio',
            'duracao_padrao'  => 1.0,
            'icone'           => '🐕',
            'categoria'       => 'Pets',
        ],
        'telhado' => [
            'label'           => 'Telhado',
            'descricao'       => 'Manutenção, reparos e impermeabilização de telhados',
            'unidade'         => 'serviço',
            'campo_valor'     => 'valor_telhado',
            'duracao_padrao'  => 4.0,
            'icone'           => '🏚️',
            'categoria'       => 'Obra',
        ],
        'piscinas' => [
            'label'           => 'Piscinas',
            'descricao'       => 'Limpeza, tratamento e manutenção de piscinas',
            'unidade'         => 'visita',
            'campo_valor'     => 'valor_piscina',
            'duracao_padrao'  => 2.0,
            'icone'           => '🏊',
            'categoria'       => 'Lazer',
        ],
        'jardins' => [
            'label'           => 'Jardins',
            'descricao'       => 'Jardinagem, poda, grama e paisagismo leve',
            'unidade'         => 'serviço',
            'campo_valor'     => 'valor_jardim',
            'duracao_padrao'  => 3.0,
            'icone'           => '🌿',
            'categoria'       => 'Exterior',
        ],
        'hidraulico' => [
            'label'           => 'Hidráulica',
            'descricao'       => 'Encanamento, vazamentos, torneiras e desentupimento',
            'unidade'         => 'visita',
            'campo_valor'     => 'valor_hidraulico',
            'duracao_padrao'  => 2.0,
            'icone'           => '🔧',
            'categoria'       => 'Reparos',
        ],
    ];

    /**
     * Fluxo de status do agendamento.
     *
     * @var array<string, string>
     */
    public array $statusLabels = [
        'pendente'               => 'Aguardando prestador',
        'aceito'                 => 'Aceito',
        'rejeitado'              => 'Rejeitado',
        'cancelado'              => 'Cancelado',
        'em_andamento'           => 'Em andamento',
        'aguardando_confirmacao' => 'Aguardando confirmação',
        'confirmado'             => 'Confirmado pelo cliente',
        'pago'                   => 'Pago',
    ];

    /** @var array<string, string> */
    public array $statusCores = [
        'pendente'               => 'gold',
        'aceito'                 => 'blue',
        'rejeitado'              => 'red',
        'cancelado'              => 'gray',
        'em_andamento'           => 'teal',
        'aguardando_confirmacao' => 'gold',
        'confirmado'             => 'green',
        'pago'                   => 'green',
    ];

    /** @return list<string> */
    public function chaves(): array
    {
        return array_keys($this->tipos);
    }

    public function ehTipoValido(string $tipo): bool
    {
        return isset($this->tipos[$tipo]);
    }

    public function label(string $tipo): string
    {
        return $this->tipos[$tipo]['label'] ?? $tipo;
    }

    public function campoValor(string $tipo): string
    {
        return $this->tipos[$tipo]['campo_valor'] ?? 'valor_diaria';
    }

    public function duracaoPadrao(string $tipo): float
    {
        return (float) ($this->tipos[$tipo]['duracao_padrao'] ?? 4.0);
    }
}
