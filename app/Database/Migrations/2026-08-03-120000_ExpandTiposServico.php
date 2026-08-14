<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Amplia nichos: telhado, piscinas, jardins, hidráulica.
 */
class ExpandTiposServico extends Migration
{
    public function up(): void
    {
        // MySQL: amplia ENUM de agendamentos
        $this->db->query("ALTER TABLE `agendamentos`
            MODIFY `tipo_servico` ENUM(
                'diarista','passeador','telhado','piscinas','jardins','hidraulico'
            ) NOT NULL");

        // Novos valores por tipo no perfil do prestador
        $fields = [
            'valor_telhado' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'valor_passeio',
            ],
            'valor_piscina' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'valor_telhado',
            ],
            'valor_jardim' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'valor_piscina',
            ],
            'valor_hidraulico' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'valor_jardim',
            ],
        ];

        // Só adiciona se ainda não existirem (idempotente em dev)
        $existing = $this->db->getFieldNames('perfis_prestador');
        foreach ($fields as $name => $def) {
            if (! in_array($name, $existing, true)) {
                $this->forge->addColumn('perfis_prestador', [$name => $def]);
            }
        }

        // CSV de tipos pode ficar maior
        $this->db->query("ALTER TABLE `perfis_prestador`
            MODIFY `tipos_servico` VARCHAR(180) NOT NULL
            COMMENT 'CSV: diarista,passeador,telhado,piscinas,jardins,hidraulico'");
    }

    public function down(): void
    {
        // Reverte apenas valores extras (mantém dados diarista/passeador)
        foreach (['valor_hidraulico', 'valor_jardim', 'valor_piscina', 'valor_telhado'] as $col) {
            if (in_array($col, $this->db->getFieldNames('perfis_prestador'), true)) {
                $this->forge->dropColumn('perfis_prestador', $col);
            }
        }

        $this->db->query("ALTER TABLE `agendamentos`
            MODIFY `tipo_servico` ENUM('diarista','passeador') NOT NULL");
    }
}
